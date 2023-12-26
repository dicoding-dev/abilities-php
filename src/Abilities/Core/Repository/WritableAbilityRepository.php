<?php

namespace Abilities\Core\Repository;

use Abilities\Core\Objects\Rule;

/**
 * A repository that focus on making changes the user abilities
 */
interface WritableAbilityRepository
{
    /**
     * Set the new ability for current user
     *
     * @param string $action The action for a given resource. Usually, this can be a CRUD model
     *                        like create, read, update, delete, or you can define your own model.
     *                        To allow all action for a given resource, you can pass star '*'.
     * @param string $resource The object 'resource' to be accessed. Must be specific for a given {@see $scope}.
     *                          A 'resource' is defined by domain expert on specific {@see $scope}.
     * @param string $scope The scope for given rules. Impacting on how the systems select the resource
     * @param mixed|null $field Can be an object, array, or a single string, int for defining specific area of {@see $resource}
     */
    public function setAbility(
        string $action,
        string $resource,
        string $scope,
        mixed $field = null,
        bool $inverted = false
    ): void;

    /**
     * Remove the capability for the current user.
     * See {@see setAbility()} for other parameters information
     */
    public function unsetAbility(
        string $action,
        string $resource,
        string $scope,
        mixed $field = null,
        bool $inverted = false
    ): void;

    /**
     * Commit the current changes to the storage
     */
    public function commitChanges(): void;

}
