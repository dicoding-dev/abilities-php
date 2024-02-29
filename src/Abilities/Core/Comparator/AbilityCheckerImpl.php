<?php

namespace Abilities\Core\Comparator;

use Abilities\Core\Compiler\RuleCompiler;
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
        $unspecifiedActionRules  = $this->compiledRules->queryRule($scope, $resource, '');

        $specificNormalRules = [];
        $starActionRules = [];
        foreach ($unspecifiedActionRules as $unspecifiedActionRule) {
            /** 1. Checking on specific inverted rules */
            if ($unspecifiedActionRule->isInverted() &&
                $unspecifiedActionRule->getResource()->matchField($field) &&
                $unspecifiedActionRule->getAction()->match($action)
            ) {
                return false; // as the correspondent user is prohibited access resource
            } elseif ($unspecifiedActionRule->getAction()->wholeAction()) {
                $starActionRules[] = $unspecifiedActionRule;
            } elseif ($unspecifiedActionRule->getAction()->get() === $action) {
                $specificNormalRules[] = $unspecifiedActionRule;
            }
        }

        /** 2. Star-<action> rules */
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

    public function hasExactRule(string|Rule $ruleOrSyntax): bool
    {
        return $this->getExactRuleOf($ruleOrSyntax) !== null;
    }

    public function getExactRuleOf(string|Rule $ruleOrSyntax): ?Rule
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
            if ($queriedRule->getResource()->isEqualWith($ruleOrSyntax->getResource())
                && $ruleOrSyntax->isInverted() === $queriedRule->isInverted()) {
                return $queriedRule;
            }
        }

        return null;
    }
}