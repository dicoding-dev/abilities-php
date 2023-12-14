<?php

namespace Core;

use Abilities\Core\Comparator\AbilityCheckerImpl;
use Abilities\Core\Objects\CompiledRules;
use Abilities\Core\RuleCompiler;

/**
 * FIRST PRECEDENCE
 * 1. Specific negated rules         -> !jobs:vacancies/4:*
 * 2. Star-<action> rules            ->  jobs:vacancies/1:*
 * 3. Other specific-<action> rules  ->  jobs:vacancies:<other>
 *
 * SECOND (NESTED) PRECEDENCE
 * 1. Star-<field> rules or Empty-<field> rules         --> jobs:vacancies/*:update or jobs:vacancies:update
 * 2. Single-<field> rules                              --> jobs:vacancies/2:update
 * 3. Array-<field> rules (OR method)                   --> jobs:vacancies/[4, 5, 6]:update
 * 4. Object-<field> rules (AND method per attributes)  --> jobs:vacancies/{"authoredBy": 22}:update
 *
 */

describe('can() feature function test', function () {
    it('must return false when the user have inverted rule', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope1:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => '!scope1:resource1/666:update'
            ],
            (object) [
                'id' => 3,
                'rule' => '!scope1:resource1/[6, 7, 8]:update'
            ],
        ]);

        $abilityChecker = new AbilityCheckerImpl($compiledRules);
        expect($abilityChecker->can('update', 'resource1', 'scope1', 666))
            ->toBeFalse();
    });

    it('must return true when user have rule with ALL action', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope1:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => 'scope1:resource1:*'
            ],
            (object) [
                'id' => 3,
                'rule' => '!scope1:resource1/[6, 7, 8]:update'
            ],
        ]);

        $abilityChecker = new AbilityCheckerImpl($compiledRules);
        expect($abilityChecker->can('update', 'resource1', 'scope1', 666))
            ->toBeTrue();
    });

    it('must return true when the rule is matched with user abilities', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope2:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => '!scope1:resource1/666:update'
            ],
            (object) [
                'id' => 3,
                'rule' => 'scope1:resource1/[6, 7, 8]:update'
            ],
            (object) [
                'id' => 4,
                'rule' => 'scope2:resource1/[6, 7, 8]:update'
            ],
        ]);

        $abilityChecker = new AbilityCheckerImpl($compiledRules);
        expect($abilityChecker->can('update', 'resource1', 'scope2', 7))
            ->toBeTrue();
    });

    it('must return false when the rule is unmatched with user abilities', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope2:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => '!scope1:resource1/666:update'
            ],
            (object) [
                'id' => 3,
                'rule' => 'scope1:resource1/{"author": 667}:update'
            ],
            (object) [
                'id' => 4,
                'rule' => 'scope2:resource1/[6, 7, 8]:update'
            ],
        ]);

        $abilityChecker = new AbilityCheckerImpl($compiledRules);
        expect($abilityChecker->can('update', 'resource1', 'scope1', (object) ['author' => 666]))
            ->toBeFalse();
    });
});

describe('cannot() feature function test', function () {
    it('must return true when the user cannot access the resource', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope2:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => '!scope1:resource1/666:update'
            ],
            (object) [
                'id' => 3,
                'rule' => 'scope1:resource1/{"author": 667}:update'
            ],
            (object) [
                'id' => 4,
                'rule' => 'scope2:resource1/[6, 7, 8]:update'
            ],
        ]);

        $user = new AbilityCheckerImpl($compiledRules);
        expect($user->cannot('update', 'resource1', 'scope1', (object) ['author' => 666]))
            ->toBeTrue();
    });
});

describe('hasRule() feature function test', function () {
    it('must return true when the rule exactly found on the user abilities', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope2:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => '!scope1:resource1/666:update'
            ],
            (object) [
                'id' => 3,
                'rule' => 'scope1:resource1/{"author": 667}:update'
            ],
            (object) [
                'id' => 4,
                'rule' => 'scope2:resource1/[6, 7, 8]:update'
            ],
        ]);

        $user = new AbilityCheckerImpl($compiledRules);
        expect($user->hasRule(RuleCompiler::compile('scope2:resource1/[6, 7, 8]:update')))
            ->toBeTrue();
    });

    it('must return false when the rule is not found on the user abilities', function () {
        $compiledRules = new CompiledRules([
            (object) [
                'id' => 1,
                'rule' => 'scope2:resource1:read'
            ],
            (object) [
                'id' => 2,
                'rule' => '!scope1:resource1/666:update'
            ],
            (object) [
                'id' => 3,
                'rule' => 'scope1:resource1/{"author": 667}:update'
            ],
            (object) [
                'id' => 4,
                'rule' => 'scope2:resource1/[6, 7, 8]:update'
            ],
        ]);

        $user = new AbilityCheckerImpl($compiledRules);
        expect($user->hasRule(RuleCompiler::compile('scope2:resource1/[6, 8]:update')))
            ->toBeFalse();
    });
});