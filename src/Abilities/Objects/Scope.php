<?php

namespace Abilities\Objects;

class Scope
{
    public function __construct(private readonly string $scope = 'global')
    {
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
