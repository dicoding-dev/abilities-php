<?php

namespace Abilities\Objects;

use Abilities\Core\RuleCompiler;

class CompiledRules
{
    private array $compiledRules = [];

    /**
     * @param string[] $rules A list of rules
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
        if (!in_array($scope, $this->compiledRules)) {
            return [];
        }

        if (!in_array($resource, $this->compiledRules[$scope])) {
            return [];
        }

        if (!in_array($action, $this->compiledRules[$scope][$resource])) {
            return [];
        }

        return $this->compiledRules[$scope][$resource][$action];
    }

    private function compile(): void
    {
        foreach ($this->rules as $rule) {
            $compiledRule = RuleCompiler::compile($rule);
            $scope = $compiledRule->getScope()->get();
            $resource = $compiledRule->getResource()->getResource();
            $action = $compiledRule->getAction()->get();

            if (!in_array($scope, $this->compiledRules, true)) {
                $this->compiledRules[$scope] = [];
            }

            if (!in_array($resource, $this->compiledRules[$scope], true)) {
                $this->compiledRules[$scope][$resource] = [];
            }

            if (!in_array($action, $this->compiledRules[$scope][$resource], true)) {
                $this->compiledRules[$scope][$resource][$action] = [];
            }

            $this->compiledRules[$scope][$resource][$action][] = $compiledRule;
        }
    }
}
