<?php

namespace Abilities\Objects;

use InvalidArgumentException;

class Action
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private readonly string $action = '*')
    {
        if (empty($this->action)) {
            throw new InvalidArgumentException('Action must not be empty');
        }
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
