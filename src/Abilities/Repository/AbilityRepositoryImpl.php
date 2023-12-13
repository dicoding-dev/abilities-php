<?php

namespace Abilities\Repository;

use Abilities\Core\AbilityChecker;
use Abilities\Core\AbilityCheckerImpl;
use Abilities\Core\RuleCompiler;
use Abilities\Objects\CompiledRules;
use Abilities\Objects\Rule;
use Abilities\Repository\AbilityRepository;
use Abilities\Storage\StorageInterface;

class AbilityRepositoryImpl implements AbilityRepository
{
    private int $currentUserId;

    private ?CompiledRules $compiledRules = null;
    private AbilityChecker $abilityChecker;

    public function __construct(
        private readonly StorageInterface $storage
    ) {
    }
    /**
     * @inheritDoc
     */
    public function addAbility(string $action, string $resource, string $scope, mixed $field = null): void
    {
        if ($this->compiledRules === null) {
            throw new \Exception("Rules still not compiled yet. Please call setUserId() to compile rules for specific user");
        }
    }

    /**
     * @inheritDoc
     */
    public function removeAbility(string $action, string $resource, string $scope, mixed $field = null): void
    {
        if ($this->compiledRules === null) {
            throw new \Exception("Rules still not compiled yet. Please call setUserId() to compile rules for specific user");
        }
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
        if ($this->compiledRules === null) {
            throw new \Exception("Rules still not compiled yet. Please call setUserId() to compile rules for specific user");
        }
    }

    /**
     * @inheritDoc
     */
    public function commitChanges(): void
    {
        if ($this->compiledRules === null) {
            throw new \Exception("Rules still not compiled yet. Please call setUserId() to compile rules for specific user");
        }


    }

    /**
     * @inheritDoc
     */
    public function setUserId(int $userId): void
    {
        $this->currentUserId = $userId;
        $rules = $this->storage->onGetRulesByUserId($userId);
        $this->compiledRules = new CompiledRules($rules);
        $this->abilityChecker = new AbilityCheckerImpl($this->compiledRules);
    }
}