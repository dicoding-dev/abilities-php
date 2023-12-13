<?php

namespace Abilities\Objects;

class Resource
{
    public function __construct(
        private readonly string $resource,
        private readonly mixed $field = null
    ) {
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getField(): mixed
    {
        return $this->field;
    }

    public function __toString(): string
    {
        if (empty($this->getField())) {
            return $this->getResource();
        }

        return $this->getResource() . "/" . json_encode($this->getField());
    }
}
