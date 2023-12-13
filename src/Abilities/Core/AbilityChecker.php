<?php

namespace Abilities\Core;

use Abilities\Objects\Rule;

/**
 * A library for supports the ability checker
 */
interface AbilityChecker
{
    /**
     * Check if the user has the special ability or not.
     *
     * @param string $action The action for a given resource. Usually, this can be a CRUD model
     *                       like create, read, update, delete, or you can define your own model.
     *                       To allow all action for a given resource, you can pass star '*'.
     * @param string $resource The object 'resource' to be accessed. Must be specific for a given {@see $scope}.
     *                         A 'resource' is defined by domain expert on specific {@see $scope}.
     * @param string $scope The scope for given rules. Impacting on how the systems select the resource
     * @param mixed|null $field
     *
     * @return bool true if the current user has the capabilities for current rule
     */
    public function can(
        string $action,
        string $resource,
        string $scope,
        mixed $field = null
    ): bool;

    /**
     * A negated approach for checking the user abilities.
     * See {@see can()} for more information.
     *
     * @return bool true if the current user does not have the capabilities for current rule
     */
    public function cannot(
        string $action,
        string $resource,
        string $scope,
        mixed $field = null
    ): bool;

    /**
     * Use the same approach as method {@see can()} does. But via customized syntax or rules.
     * Via Syntax :
     * <scope>:<resource>/<field>:<action>
     *
     *
     * @param Rule|string $ruleOrSyntax A syntax (string) for defining rules or with using {@see Rule}
     * @return bool true if the current user has the rule
     */
    public function hasRule(Rule|string $ruleOrSyntax): bool;
}
