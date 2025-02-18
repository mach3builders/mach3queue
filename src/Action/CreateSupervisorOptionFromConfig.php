<?php

namespace Mach3queue\Action;

use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;

class CreateSupervisorOptionFromConfig
{
    public static function create(
        MasterSupervisor $master,
        string $name,
        array $options
    ): SupervisorOptions {
        $supervisor_options = SupervisorOptions::fromConfig($name, $options);
        $supervisor_options->master = $master->name();

        return $supervisor_options;
    }
}