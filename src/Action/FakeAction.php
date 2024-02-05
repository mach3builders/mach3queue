<?php

namespace Mach3queue\Action;

class FakeAction
{
    public function __construct(private $callback)
    {
    }

    public function execute(...$args): void
    {
        ($this->callback)(...$args);
    }
}