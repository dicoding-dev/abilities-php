<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Comparator\AbilityCheckerImpl;
use Abilities\Core\Objects\Action;
use Abilities\Core\Objects\CompiledRules;
use Abilities\Core\Objects\Enums\FieldType;
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

            if ($this->matchResource($rule->getResource(), $resource, $field) &&
                $this->matchAction($rule->getAction(), $action) &&
                $rule->isInverted() === $inverted
            ) {
                if ($rule->getResource()->getFieldType() === FieldType::ARRAY) {
                    $newRule = $this->removeItemRuleFromArray($rule, $field);
                    if (!empty($newRule)) {
                        $this->storage->onUpdateRule($rule->getRuleId(), $this->currentUserId, "$newRule");
                        continue;
                    }
                }

                $this->storage->onDeleteSpecificRule($rule->getRuleId(), $this->currentUserId);
            }
        }

        $this->refresh();
    }

    private function removeItemRuleFromArray(Rule $rule, int|array|string $fields): ?Rule
    {
        $newFields = [];
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $steadyFieldCount = 0;
        foreach ($rule->getResource()->getField() as $oldField) {
            if (!in_array($oldField, $fields)) {
                $newFields[] = $oldField;
                $steadyFieldCount++;
            }
        }

        if ($steadyFieldCount === 0) {
            return null;
        }

        if ($steadyFieldCount === 1) {
            return $rule->withNewField($newFields[0]);
        }

        return $rule->withNewField($newFields);
    }

    private function matchResource(
        Resource $resource,
        string $checkedResource,
        mixed $checkedResourceField
    ): bool {
        if ($checkedResource === '*' ) {
            return $checkedResourceField === '*' || $resource->matchField($checkedResourceField);
        }

        if ($resource->getResourceString() !== $checkedResource) {
            return false;
        }

        return $resource->matchField($checkedResourceField);
    }

    private function matchAction(Action $action, string $checkedAction): bool
    {
        if ($checkedAction === '*') {
            return true;
        }

        return $action->match($checkedAction);
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
