<?php

namespace Abilities\Core\Repository;

use Abilities\Core\AbilityChecker;
use Abilities\Core\Objects\Rule;

interface AbilityRepository
{
    /**
     * Add new capability for current user
     *
     * @param string $action The action for a given resource. Usually, this can be a CRUD model
     *                       like create, read, update, delete, or you can define your own model.
     *                       To allow all action for a given resource, you can pass star '*'.
     * @param string $resource The object 'resource' to be accessed. Must be specific for a given {@see $scope}.
     *                         A 'resource' is defined by domain expert on specific {@see $scope}.
     * @param string $scope The scope for given rules. Impacting on how the systems select the resource
     * @param mixed|null $field Can be an object, array, or a single string, int for defining specific area of {@see $resource}
     */
    public function addAbility(
        string $action,
        string $resource,
        string $scope,
        mixed $field = null,
        bool $inverted = false
    ): void;

    /**
     * Add abilities for current user
     *
     * @param Rule[]|string[] $rules
     */
    public function addAbilities(array $rules): void;

    /**
     * Remove the capability for the current user.
     * See {@see addAbility()} for other parameters information
     */
    public function removeAbility(
        string $action,
        string $resource,
        string $scope,
        mixed $field = null,
        bool $inverted = false
    ): void;

    /**
     * Remove abilities for current user
     *
     * @param Rule[]|string[] $rules
     */
    public function removeAbilities(array $rules): void;

    /**
     * Get the implementation of {@see AbilityChecker}.
     */
    public function getChecker(): AbilityChecker;

    /**
     * Update the {@see $old} rule into {@see $new} rule
     */
    public function update(Rule|string $old, Rule|string $new): void;

    /**
     * Commit the current changes to the storage
     */
    public function commitChanges(): void;

    /**
     * Set the current session for specific {@param int|string $userId}
     */
    public function setUserId(int|string $userId): void;
}
