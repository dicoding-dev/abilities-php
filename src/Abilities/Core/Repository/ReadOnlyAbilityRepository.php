<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Comparator\AbilityCheckerImpl;
use Abilities\Core\Objects\CompiledRules;
use Abilities\Core\Repository\AbilityReaderRepository;
use Abilities\Core\Storage\StorageInterface;

class ReadOnlyAbilityRepository implements AbilityReaderRepository
{
    public function __construct(
        private readonly int|string $currentUserId,
        private readonly StorageInterface $storage
    ) {
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
        $rules = $this->storage->onGetRulesByUserId($this->currentUserId);
        $compiledRules = new CompiledRules($rules);
        return new AbilityCheckerImpl($compiledRules);
    }
}
