<?php

namespace Abilities\Objects;

use InvalidArgumentException;

class Scope
{
    public function __construct(private string $scope = 'global')
    {
        $this->scope = trim($this->scope);
        if (empty($this->scope)) {
            throw new InvalidArgumentException('Scope must not be empty');
        }
    }

    public function get(): string
    {
        return $this->scope;
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
