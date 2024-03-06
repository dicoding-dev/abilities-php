<?php

namespace Abilities\Core\Objects;

use Abilities\Core\Compiler\RuleCompiler;

class CompiledRules
{
    private array $compiledRules = [];

    /**
     * @param list<object{
     *      id: int,
     *      rule: string
     *  }> $rules A list of rules with the rule ID
     */
    public function __construct(private readonly array $rules)
    {
        $this->compile();
    }

    /**
     * @return Rule[] array of rules
     */
    public function queryRule(string $scope, string $resource, string $action): array
    {
        if (!array_key_exists($scope, $this->compiledRules)) {
            return [];
        }

        if ($this->isWhole($resource)) {
            $result = [];
            $isWholeAction = $this->isWhole($action);
            foreach ($this->compiledRules[$scope] as $actions) {
                foreach ($actions as $arrayOfRule) {
                    /** @var Rule $rule */
                    foreach ($arrayOfRule as $rule) {
                        if ($isWholeAction) {
                            $result[] = $rule;
                            continue;
                        }

                        if ($this->matchAction($rule->getAction(), $action)) {
                            $result[] = $rule;
                        }
                    }
                }
            }

            return $result;
        }

        if (!array_key_exists($resource, $this->compiledRules[$scope])) {
            return [];
        }

        // if the action not specific, it will retrieve all actions (include global too)
        if ($this->isWhole($action)) {
            $unspecifiedActions = $this->compiledRules[$scope][$resource];
            $result = [];
            array_walk_recursive($unspecifiedActions, function($a) use (&$result) { $result[] = $a; });
            return $result;
        }

        if (!array_key_exists($action, $this->compiledRules[$scope][$resource])) {
            return [];
        }

        return $this->compiledRules[$scope][$resource][$action];
    }

    private function matchAction(Action $action, string $checkedAction): bool
    {
        if ($checkedAction === '*' || $checkedAction === '') {
            return true;
        }

        return $action->get() === $checkedAction;
    }

    private function compile(): void
    {
        foreach ($this->rules as $rule) {
            $compiledRule = RuleCompiler::compile($rule->rule);
            $compiledRule->setRuleId($rule->id);

            $scope = $compiledRule->getScope()->get();
            $resource = $compiledRule->getResource()->getResource();
            $action = $compiledRule->getAction()->get();

            if (!array_key_exists($scope, $this->compiledRules)) {
                $this->compiledRules[$scope] = [];
            }

            if (!array_key_exists($resource, $this->compiledRules[$scope])) {
                $this->compiledRules[$scope][$resource] = [];
            }

            if (!array_key_exists($action, $this->compiledRules[$scope][$resource])) {
                $this->compiledRules[$scope][$resource][$action] = [];
            }

            if ($compiledRule->getResource()->allField()) {
                array_unshift($this->compiledRules[$scope][$resource][$action], $compiledRule);
            } else {
                $this->compiledRules[$scope][$resource][$action][] = $compiledRule;
            }
        }
    }

    private function isWhole(string $str): bool
    {
        return empty($str) || $str === '*';
    }
}
