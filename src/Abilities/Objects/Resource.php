<?php

namespace Abilities\Objects;

use InvalidArgumentException;

class Resource
{
    public function __construct(
        private string $resource,
        private readonly mixed $field = null
    ) {
        $this->resource = trim($this->resource);
        if (empty($this->resource)) {
            throw new InvalidArgumentException('Resource must not be empty');
        }
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getField(): mixed
    {
        return $this->field;
    }

    public function allField(): bool
    {
        if (empty($this->getField())) {
            return true;
        }

        return $this->getField() === '*';
    }

    public function __toString(): string
    {
        if (empty($this->getField())) {
            return $this->getResource();
        }

        return $this->getResource() . "/" . json_encode($this->getField());
    }
}
