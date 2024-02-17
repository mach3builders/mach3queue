<?php

namespace Mach3queue\Action;

use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;

class CreateSupervisorOptionFromConfig
{
    static function create(
        MasterSupervisor $master, 
        string $supervisor, 
        array $options
    ): SupervisorOptions
    {
        $supervisor_options = SupervisorOptions::fromConfig($supervisor, $options);
        $supervisor_options->master = $master->name();

        return $supervisor_options;
    }
}