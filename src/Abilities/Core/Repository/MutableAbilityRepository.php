<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Objects\Rule;

interface MutableAbilityRepository extends AbilityReaderRepository, WritableAbilityRepository
{
}
