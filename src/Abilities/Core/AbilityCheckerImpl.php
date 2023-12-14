<?php

namespace Abilities\Core;

use Abilities\Core\Objects\CompiledRules;
use Abilities\Core\Objects\Rule;

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
        $rule = $this->getRuleOf($ruleOrSyntax);
        return $rule !== null;
    }

    /**
     * @inheritDoc
     */
    public function getRuleOf(string|Rule $ruleOrSyntax): ?Rule
    {
        if(is_string($ruleOrSyntax)) {
            $ruleOrSyntax = RuleCompiler::compile($ruleOrSyntax);
        }

        $queriedRules = $this->compiledRules->queryRule(
            $ruleOrSyntax->getScope()->get(),
            $ruleOrSyntax->getResource()->getResource(),
            $ruleOrSyntax->getAction()->get()
        );

        foreach ($queriedRules as $queriedRule) {
            if ($queriedRule->getResource() == $ruleOrSyntax->getResource()) {
                return $queriedRule;
            }
        }

        return null;
    }
}