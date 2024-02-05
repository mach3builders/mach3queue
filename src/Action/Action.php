<?php

namespace Mach3queue\Action;

interface Action
{
    public function execute(...$args): void;
}