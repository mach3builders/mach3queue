<?php

namespace Mach3queue\Supervisor;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mach3queue\Action\CreateSupervisorOptionFromConfig;
use Mach3queue\ListensForSignals;
use Mach3queue\Process\SupervisorProcess;
use Symfony\Component\Process\Process;
use Throwable;

class MasterSupervisor
{
    use ListensForSignals;
    
    public Collection $supervisors;

    public Closure $output;

    public bool $working = true;

    public string $name;

    public MasterActions $actions;

    public array $config;

    public function __construct(
        array $config,
        MasterActions $actions = new MasterActions
    ) {
        $this->config = $config;
        $this->name = static::name();
        $this->supervisors = new Collection;
        $this->actions = $actions;
        $this->createSupervisorsFromConfig($config);
    }

    public function monitor(): void
    {
        $this->listenForSignals();
        $this->persist();

        while (true) {
            sleep(1);

            $this->loop();
        }
    }

    public function loop(): void
    {
        $this->processPendingSignals();
        $this->monitorSupervisors();

        $this->persist();
        $this->actions->expireSupervisors();
        $this->actions->trimOldJobs($this->config);
    }

    private function monitorSupervisors(): void
    {
        if (! $this->working) {
            return;
        }

        $this->supervisors->each->monitor();
        $this->rejectSupervisorsThatAreDead();
    }

    public function handleOutputUsing(Closure $callback): static
    {
        $this->output = $callback;

        return $this;
    }
    
    public static function name(): string
    {
        static $token;

        if (! $token) {
            $token = Str::random(4);
        }

        return Str::slug(gethostname()).'-'.$token;
    }

    public function pid(): false|int
    {
        return getmypid();
    }

    public function restart(): void
    {
        $this->working = true;
        $this->supervisors->each->restart();
    }

    public function pause(): void
    {
        $this->working = false;
        $this->supervisors->each->pause();
    }

    public function continue(): void
    {
        $this->working = true;
        $this->supervisors->each->continue();
    }

    public function terminate(): void
    {
        $this->working = false;

        $this->supervisors->each->terminate();

        SupervisorRepository::forget($this->name);

        $this->waitForSupervisorsToTerminate();
        
        $this->exit(1);
    }


    public function addSupervisorProcess(
        SupervisorOptions $options,
        Process $process
    ): void {
        $supervisor_process = new SupervisorProcess(
            $options, 
            $process, 
            fn ($type, $line) => $this->output->call($this, $type, $line)
        );
        
        $this->supervisors->push($supervisor_process);
    }

    private function createSupervisorsFromConfig(array $config): void
    {
        foreach ($config as $supervisor => $options) {
            $supervisor_options = CreateSupervisorOptionFromConfig::create(
                $this,
                $supervisor,
                $options
            );

            $process = Process::fromShellCommandline(
                $supervisor_options->toSupervisorCommand(),
                $supervisor_options->directory
            )->setTimeout(null)->disableOutput();
            
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
    
    public function getLongestTimeoutSupervisor(): int
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

    public function persist(): void
    {
        SupervisorRepository::updateOrCreateMaster($this);
    }

    protected function exit(int $status = 0): void
    {
        exit($status);
    }
}