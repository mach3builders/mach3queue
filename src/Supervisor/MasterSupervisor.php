<?php

namespace Mach3queue\Supervisor;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mach3queue\ListensForSignals;
use Mach3queue\Process\SupervisorProcess;
use Mach3queue\SuperVisor\Supervisor;
use Symfony\Component\Process\Process;
use Throwable;

class MasterSupervisor
{
    use ListensForSignals;
    
    public Collection $supervisors;

    public Closure $output;

    public bool $is_working = true;

    public function __construct(array $config)
    {
        $this->supervisors = new Collection;
        $this->createSupervisorsFromConfig($config);
    }

    public function monitor(): void
    {
        $this->listenForSignals();

        while (true) {
            sleep(1);

            $this->loop();
        }
    }

    public function loop(): void
    {
        try {
            $this->processPendingSignals();
            $this->monitorSupervisors();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function monitorSupervisors(): void
    {
        if (! $this->is_working) {
            return;
        }

        $this->supervisors->each->monitor();
        $this->rejectSupervisorsThatAreDead();
    }

    public function handleOutputUsing(Closure $callback)
    {
        $this->output = $callback;

        return $this;
    }
    
    public static function name()
    {
        static $token;

        if (! $token) {
            $token = Str::random(4);
        }

        return Str::slug(gethostname()).'-'.$token;
    }

    public function pid()
    {
        return getmypid();
    }

    public function memoryUsageInMb()
    {
        return memory_get_usage() / 1024 / 1024;
    }

    public function restart(): void
    {
        $this->is_working = true;
        $this->supervisors->each->restart();
    }

    public function pause(): void
    {
        $this->is_working = false;
        $this->supervisors->each->pause();
    }

    public function continue(): void
    {
        $this->is_working = true;
        $this->supervisors->each->continue();
    }

    public function terminate(): void
    {
        $this->is_working = false;

        $this->supervisors->each->terminate();

        // TODO remove master supervisor from the repository so it can 
        // start a new one without waiting for this one to die out.

        $this->waitForSupervisorsToTerminate();

        exit(1);
    }


    public function addSupervisorProcess(
        SupervisorOptions $options,
        Process $process
    ): void {
        $supervisor_process = new SupervisorProcess($options, $process);
        
        $this->supervisors->push($supervisor_process);
    }

    private function createSupervisorsFromConfig(array $config): void
    {
        foreach ($config as $supervisor => $options) {
            $supervisor_options = SupervisorOptions::fromConfig($supervisor, $options);
            $command = $supervisor_options->toWorkerCommand();
            $directory = $supervisor_options->directory;
            $process = Process::fromShellCommandline($command, $directory)
                ->setTimeout(null)
                ->disableOutput();

            $this->addSupervisorProcess($supervisor_options, $process);
        }
    }

    private function rejectSupervisorsThatAreDead(): void
    {
        $this->supervisors = $this->supervisors->reject->dead;
    }

    private function waitForSupervisorsToTerminate(): void
    {
        $startedTerminating = CarbonImmutable::now();
        $longest = $this->getLongestTimeoutSupervisor();

        while (count($this->supervisors->filter->isRunning())) {
            if ($this->passedLongestTimeout($startedTerminating, $longest)) {
                break;
            }

            sleep(1);
        }
    }
    
    private function getLongestTimeoutSupervisor(): int
    {
        return $this->supervisors->max('options.timeout');
    }

    private function passedLongestTimeout(
        CarbonImmutable $startedTerminating,
        int $longest
    ): bool {
        return CarbonImmutable::now()
            ->subSeconds($longest)
            ->gte($startedTerminating);
    }
}