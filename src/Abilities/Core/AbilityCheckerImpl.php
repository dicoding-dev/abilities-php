<?php

namespace Abilities\Core;

use Abilities\Core\AbilityChecker;
use Abilities\Objects\CompiledRules;
use Abilities\Objects\Rule;

class AbilityCheckerImpl implements AbilityChecker
{
    public function __construct(private readonly CompiledRules $compiledRules)
    {
    }

    /**
     * @inheritDoc
     */
    public function can(string $action, string $resource, string $scope, mixed $field = null): bool
    {
        // TODO: Implement can() method.
    }

    /**
     * @inheritDoc
     */
    public function cannot(string $action, string $resource, string $scope, mixed $field = null): bool
    {
        // TODO: Implement cannot() method.
    }

    /**
     * @inheritDoc
     */
    public function hasRule(string|Rule $ruleOrSyntax): bool
    {
        // TODO: Implement hasRule() method.
    }
}