<?php

namespace Abilities\Core\Objects;

use Exception;

class Rule
{
    private ?int $ruleId = null;

    public function __construct(
        private readonly Scope $scope,
        private readonly Resource $resource,
        private readonly Action $action,
        private readonly bool $isInverted = false
    ) {
    }

    public function setRuleId(int $id): void
    {
        $this->ruleId = $id;
    }

    /**
     * @throws Exception
     */
    public function getRuleId(): int
    {
        if ($this->ruleId === null){
            throw new Exception("Don't forget to call 'setRuleId()' before get the rule ID");
        }

        return $this->ruleId;
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function isInverted(): bool
    {
        return $this->isInverted;
    }

    public function __toString(): string
    {
        return ($this->isInverted() ? '!' : '') . $this->getScope() . ':' . $this->getResource() . ':' . $this->getAction();
    }

    public function withNewField(mixed $field): self
    {
        return new self(
            $this->getScope(),
            new Resource($this->getResource()->getResource(), $field),
            $this->getAction()
        );
    }
}
