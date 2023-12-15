<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Comparator\AbilityChecker;

/**
 * A repository that focus on reading the user abilities
 */
interface AbilityReaderRepository
{
    /**
     * Get the all abilities for the current user's session
     * @return string[] user's current ability
     */
    public function getAbilityRules(): array;

    /**
     * Get the implementation of {@see AbilityChecker}.
     */
    public function getChecker(): AbilityChecker;
}
