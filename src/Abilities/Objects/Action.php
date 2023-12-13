<?php

namespace Abilities\Objects;

class Action
{
    public function __construct(private readonly string $action = '*')
    {
    }

    public function get(): string
    {
        return $this->action;
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
