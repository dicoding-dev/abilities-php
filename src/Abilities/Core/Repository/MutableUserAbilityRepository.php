<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Comparator\AbilityCheckerImpl;
use Abilities\Core\Compiler\RuleCompiler;
use Abilities\Core\Objects\Action;
use Abilities\Core\Objects\CompiledRules;
use Abilities\Core\Objects\Resource;
use Abilities\Core\Objects\Rule;
use Abilities\Core\Objects\Scope;
use Abilities\Core\Storage\StorageInterface;

/** @todo plan next is defining how we can use commit-approach */
class MutableUserAbilityRepository implements MutableAbilityRepository
{
    private ?CompiledRules $compiledRules = null;
    private AbilityChecker $abilityChecker;

    /** @tell for plan commit-approach */
    private array $changedRules = [
        'created' => [],
        'updated' => [],
        'deleted' => []
    ];

    public function __construct(
        private readonly int|string $currentUserId,
        private readonly StorageInterface $storage
    ) {
        $this->refresh();
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
    public function getAbilityRules(): array
    {
        return array_map(
            fn(object $object) => $object->rule,
            $this->storage->onGetRulesByUserId($this->currentUserId)
        );
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

        if ($existingRule === null) {
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
            if ($existingRule !== null) {
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

    private function refresh(): void
    {
        $rules = $this->storage->onGetRulesByUserId($this->currentUserId);
        $this->compiledRules = new CompiledRules($rules);
        $this->abilityChecker = new AbilityCheckerImpl($this->compiledRules);
    }
}
