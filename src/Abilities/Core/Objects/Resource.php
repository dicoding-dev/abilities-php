<?php

namespace Abilities\Core\Objects;

use Abilities\Core\Objects\Enums\FieldType;
use InvalidArgumentException;

class Resource
{
    private FieldType $fieldType;

    public function __construct(
        private string $resource,
        private readonly mixed $field = null
    ) {
        $this->resource = trim($this->resource);
        if (empty($this->resource)) {
            throw new InvalidArgumentException('Resource must not be empty');
        }

        if (!preg_match('/^([a-zA-Z0-9_\-])+$/', $this->resource)) {
            throw new InvalidArgumentException(
                'Invalid resource naming. Please use a combination of lowercase letter, number, dash and underscore only'
            );
        }

        $this->processField();
    }

    private function processField(): void
    {
        if (empty($this->field) || $this->field === '*') {
            $this->fieldType = FieldType::ALL;
            return;
        }

        if ($this->isSingularField($this->field)) {
            $this->fieldType = FieldType::SINGULAR_FIELD;
            return;
        }

        if (is_object($this->field)) {
            $this->fieldType = FieldType::OBJECT;
            return;
        }

        if (array_is_list($this->field)) {
            $this->fieldType = FieldType::ARRAY;
            return;
        }

        throw new InvalidArgumentException(
            'Invalid field argument. Field does not support associative or non-trimmed string'
        );
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
        return $this->fieldType === FieldType::ALL;
    }

    public function matchField(mixed $field): bool
    {
        if ($this->allField()) {
            return true;
        }

        if (empty($field)) {
            return false;
        }

        if ($this->fieldType === FieldType::SINGULAR_FIELD) {
            if (!$this->isSingularField($field)) {
                return false;
            }

            return "" . $this->field === "$field";
        }

        if ($this->fieldType === FieldType::ARRAY) {
            if (is_array($field)) {
                foreach ($field as $fieldItem) {
                    if(!in_array($fieldItem, $this->field)) {
                        return false;
                    }
                }
                return true;
            }

            return in_array($field, $this->field);
        }

        if ($this->fieldType === FieldType::OBJECT) {
            return $field == $this->field;
        }

        return false;
    }

    public function match(string $resource, mixed $field): bool
    {
        if (!$this->matchField($field)) {
            return false;
        }

        if (empty($resource) || $resource === '*') {
            return true;
        }

        return $this->getResource() === $resource;
    }

    public function getFieldType(): FieldType
    {
        return $this->fieldType;
    }

    public function isEqualWith(self $other): bool
    {
        if ($other->getResource() !== $this->getResource()) {
            return false;
        }

        if ($other->getFieldType() !== $this->getFieldType()) {
            return false;
        }

        return match ($this->getFieldType()) {
            FieldType::ALL => true,
            FieldType::SINGULAR_FIELD, FieldType::OBJECT => $other->getField() == $this->getField(),
            FieldType::ARRAY => empty(array_diff($this->getField(), $other->getField())),
        };
    }

    private function isSingularField(mixed $field): bool
    {
        return is_string($field) || is_int($field);
    }

    public function __toString(): string
    {
        if ($this->allField()) {
            return $this->getResource() . "/*";
        }

        if ($this->fieldType === FieldType::SINGULAR_FIELD) {
            return $this->getResource() . "/" . $this->getField();
        }

        return $this->getResource() . "/" . json_encode($this->getField());
    }
}
