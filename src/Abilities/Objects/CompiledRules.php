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
        if (!array_key_exists($scope, $this->compiledRules)) {
            return [];
        }

        if (!array_key_exists($resource, $this->compiledRules[$scope])) {
            return [];
        }

        if (!array_key_exists($action, $this->compiledRules[$scope][$resource])) {
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

            if (!array_key_exists($scope, $this->compiledRules)) {
                $this->compiledRules[$scope] = [];
            }

            if (!array_key_exists($resource, $this->compiledRules[$scope])) {
                $this->compiledRules[$scope][$resource] = [];
            }

            if (!array_key_exists($action, $this->compiledRules[$scope][$resource])) {
                $this->compiledRules[$scope][$resource][$action] = [];
            }

            $this->compiledRules[$scope][$resource][$action][] = $compiledRule;
        }
    }
}
