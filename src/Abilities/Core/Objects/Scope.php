<?php

namespace Abilities\Core\Objects;

use InvalidArgumentException;

class Scope
{
    public function __construct(private string $scope = 'global')
    {
        $this->scope = trim($this->scope);
        if (empty($this->scope)) {
            throw new InvalidArgumentException('Scope must not be empty');
        }

        if (!preg_match('/^([a-zA-Z0-9_\-])+$/', $this->scope)) {
            throw new InvalidArgumentException(
                'Invalid scope naming. Please use a combination of lowercase letter, number, dash and underscore only'
            );
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
