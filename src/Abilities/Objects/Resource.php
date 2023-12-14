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

    public function matchField(mixed $field): bool
    {
        if ($this->allField()) {
            return true;
        }

        if (empty($field)) {
            return false;
        }

        if ($this->isIntOrStringField($this->field) && $this->isIntOrStringField($field)) {
            return "" . $this->field === "$field";
        }

        if (is_array($this->field)) {
            if (is_array($field)) {
                foreach ($field as $fieldItem) {
                    if(!in_array($fieldItem, $this->field)) {
                        return false;
                    }
                }
                return true;
            } else {
                return in_array($field, $this->field);
            }
        }

        if (is_object($this->field) && is_object($field)) {
            return $field == $this->field;
        }

        return false;
    }

    private function isIntOrStringField(mixed $field): bool
    {
        return is_string($field) || is_int($field);
    }

    public function __toString(): string
    {
        if (empty($this->getField())) {
            return $this->getResource();
        }

        return $this->getResource() . "/" . json_encode($this->getField());
    }
}
