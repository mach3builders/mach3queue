<?php

namespace Mach3queue\Queue;

interface Queueable
{
    public function handle(): void;
}
