<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Comparator\AbilityCheckerImpl;
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
    public function setAbility(string $action, string $resource, string $scope, mixed $field = null, bool $inverted = false): void
    {
        if ($this->compiledRules === null) {
            throw new \Exception('Empty compiled rules');
        }

        $composedNewRule = new Rule(
            new Scope($scope),
            new Resource($resource, $field),
            new Action($action),
            $inverted
        );

        $unspecifiedActionRules = $this->compiledRules->queryRule($scope, $resource, '');
        $sameActionRulesWithUpdatedRule = [];
        $sameResourceScopeWithUpdatedRule = [];

        foreach ($unspecifiedActionRules as $unspecifiedActionRule) {
            if ($unspecifiedActionRule->getAction()->get() === '*') {
                if ($unspecifiedActionRule->getResource()->isEqualWith($composedNewRule->getResource()) &&
                    $unspecifiedActionRule->isInverted() === $composedNewRule->isInverted()
                ) {
                    $this->storage->onDeleteSpecificRule($unspecifiedActionRule->getRuleId(), $this->currentUserId);
                    $this->storage->onInsertNewRule($this->currentUserId, "$composedNewRule");
                    $this->refresh(); // todo: make update not retrieving all from DB
                    return;
                }
                continue;
            }

            if ($unspecifiedActionRule->getAction()->get() === $action) {
                $sameActionRulesWithUpdatedRule[] = $unspecifiedActionRule;
            } else {
                $sameResourceScopeWithUpdatedRule[] = $unspecifiedActionRule;
            }
        }

        if ($composedNewRule->getAction()->wholeAction()) {
            foreach ($sameResourceScopeWithUpdatedRule as $currentRule) {
                if ($currentRule->getResource()->isEqualWith($composedNewRule->getResource())&&
                    $currentRule->isInverted() === $composedNewRule->isInverted()
                ) {
                    $this->storage->onDeleteSpecificRule($currentRule->getRuleId(), $this->currentUserId);
                }
            }
            $this->storage->onInsertNewRule($this->currentUserId, "$composedNewRule");
            $this->refresh();
            return;
        }

        foreach ($sameActionRulesWithUpdatedRule as $currentRule) {
            if ($currentRule->getResource()->isEqualWith($composedNewRule->getResource())) {
                if ($currentRule->isInverted() !== $composedNewRule->isInverted()) {
                    $this->storage->onDeleteSpecificRule($currentRule->getRuleId(), $this->currentUserId);
                    break;
                }
                return;
            }
        }

        $this->storage->onInsertNewRule($this->currentUserId, "$composedNewRule");
        $this->refresh();
    }

    /**
     * @inheritDoc
     */
    public function unsetAbility(string $action, string $resource, string $scope, mixed $field = null, bool $inverted = false): void
    {
        if ($this->compiledRules === null) {
            throw new \Exception('Empty compiled rules');
        }

        $queriedRules = $this->compiledRules->queryRule($scope, $resource, $action);
        foreach ($queriedRules as $rule) {

            if ($rule->getResource()->match($resource, $field) &&
                $rule->getAction()->match($action) &&
                $rule->isInverted() === $inverted
            ) {
                $this->storage->onDeleteSpecificRule($rule->getRuleId(), $this->currentUserId);
            }
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
    public function getChecker(): AbilityChecker
    {
        return $this->abilityChecker;
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
