<?php

namespace Abilities\Core;

use Abilities\Objects\CompiledRules;
use Abilities\Objects\Rule;

class AbilityCheckerImpl implements AbilityChecker
{
    public function __construct(
        private readonly CompiledRules $compiledRules
    ) {
    }

    /**
     * @inheritDoc
     */
    public function can(string $action, string $resource, string $scope, mixed $field = null): bool
    {
        $specificActionRules  = $this->compiledRules->queryRule($scope, $resource, $action);
        $specificNormalRules = [];

        foreach ($specificActionRules as $specificActionRule) {
            if ($specificActionRule->isInverted()) {
                /** 1. Checking on specific inverted rules */
                if ($specificActionRule->getResource()->matchField($field)) {
                    return false; // as the correspondent user is prohibited access resource
                }
            } else {
                $specificNormalRules[] = $specificActionRule;
            }
        }

        /** 2. Star-<action> rules */
        $starActionRules = $this->compiledRules->queryRule($scope, $resource, '*');
        foreach ($starActionRules as $starActionRule) {
            if ($starActionRule->getResource()->matchField($field)) {
                return !$starActionRule->isInverted();
            }
        }

        /** 3. Other specific-<action> rules */
        foreach ($specificNormalRules as $specificNormalRule) {
            if ($specificNormalRule->getResource()->matchField($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function cannot(string $action, string $resource, string $scope, mixed $field = null): bool
    {
        return !$this->can($action, $resource, $scope, $field);
    }

    /**
     * @inheritDoc
     */
    public function hasRule(string|Rule $ruleOrSyntax): bool
    {
        // TODO: Implement hasRule() method.
    }
}