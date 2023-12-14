<?php

namespace Abilities\Repository;

use Abilities\Core\AbilityChecker;
use Abilities\Core\AbilityCheckerImpl;
use Abilities\Core\RuleCompiler;
use Abilities\Objects\Action;
use Abilities\Objects\CompiledRules;
use Abilities\Objects\Resource;
use Abilities\Objects\Rule;
use Abilities\Objects\Scope;
use Abilities\Storage\StorageInterface;

/** @todo plan next is defining how we can use commit-approach */
class AbilityRepositoryImpl implements AbilityRepository
{
    private int|string $currentUserId;

    private ?CompiledRules $compiledRules = null;
    private AbilityChecker $abilityChecker;

    /** @tell for plan commit-approach */
    private array $changedRules = [
        'created' => [],
        'updated' => [],
        'deleted' => []
    ];

    public function __construct(
        private readonly StorageInterface $storage
    ) {
    }
    /**
     * @inheritDoc
     */
    public function addAbility(string $action, string $resource, string $scope, mixed $field = null, bool $inverted = false): void
    {
        $rule = new Rule(
            new Scope($scope),
            new Resource(
                $resource,
                $field
            ),
            new Action($action),
            $inverted
        );

        if ($this->getChecker()->hasRule($rule)) {
            return;
        }

        $this->storage->onInsertNewRule($this->currentUserId, "$rule");
        $this->refresh();
    }

    /**
     * @inheritDoc
     */
    public function addAbilities(array $rules): void
    {
        foreach ($rules as $rule) {
            if (is_string($rule)){
                $compiledRule = RuleCompiler::compile($rule);
                $this->storage->onInsertNewRule($this->currentUserId, "$compiledRule");
                continue;
            }

            $this->storage->onInsertNewRule($this->currentUserId, "$rule");
        }

        $this->refresh();
    }

    /**
     * @inheritDoc
     */
    public function removeAbility(string $action, string $resource, string $scope, mixed $field = null, bool $inverted = false): void
    {
        $rule = new Rule(
            new Scope($scope),
            new Resource(
                $resource,
                $field
            ),
            new Action($action),
            $inverted
        );

        $existingRule = $this->getChecker()->getRuleOf($rule);

        if ($existingRule) {
            return;
        }

        $this->storage->onDeleteSpecificRule($existingRule->getRuleId());
        $this->refresh();
    }

    public function removeAbilities(array $rules): void
    {
        foreach ($rules as $rule) {
            if (is_string($rule)){
                $rule = RuleCompiler::compile($rule);
            }

            $existingRule = $this->getChecker()->getRuleOf($rule);
            if (!$existingRule) {
                $this->storage->onDeleteSpecificRule($existingRule->getRuleId());
            }
        }

        $this->refresh();
    }

    /**
     * @inheritDoc
     */
    public function getChecker(): AbilityChecker
    {
        if ($this->compiledRules === null) {
            throw new \Exception("Rules still not compiled yet. Please call setUserId() to compile rules for specific user");
        }

        return $this->abilityChecker;
    }

    /**
     * @inheritDoc
     */
    public function update(Rule|string $old, Rule|string $new): void
    {
        if(is_string($old)) {
            $old = RuleCompiler::compile($old);
        }

        if(is_string($new)) {
            $new = RuleCompiler::compile($new);
        }

        $existingRule = $this->getChecker()->getRuleOf($old);
        if($existingRule === null) {
            return;
        }

        $this->storage->onUpdateRule($existingRule->getRuleId(), "$new");
        $this->refresh();
    }



    /**
     * @inheritDoc
     */
    public function commitChanges(): void
    {
        throw new \Exception('Not implemented yet!');
    }

    /**
     * @inheritDoc
     */
    public function setUserId(int|string $userId): void
    {
        $this->currentUserId = $userId;
        $this->refresh();
    }

    private function refresh(): void
    {
        $rules = $this->storage->onGetRulesByUserId($this->currentUserId);
        $this->compiledRules = new CompiledRules($rules);
        $this->abilityChecker = new AbilityCheckerImpl($this->compiledRules);
    }
}