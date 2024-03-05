<?php

use Abilities\Core\Objects\CompiledRules;
use Abilities\Core\Objects\Rule;

describe("Compile and query rules", function () {
    $rules = [
        (object) [
            'id' => 1,
            'rule' => 'scope1:resource1/666:*'
        ],
        (object) [
            'id' => 2,
            'rule' => 'scope1:resource1/5:read'
        ],
        (object) [
            'id' => 3,
            'rule' => 'scope1:resource1/[10,11,12]:read'
        ],
        (object) [
            'id' => 4,
            'rule' => 'scope1:resource1/{"expired":true}:read'
        ],
        (object) [
            'id' => 5,
            'rule' => 'scope2:resource1:read'
        ],
        (object) [
            'id' => 6,
            'rule' => 'scope1:resource2:update'
        ],
        (object) [
            'id' => 7,
            'rule' => 'scope2:resource1/666:read'
        ],
    ];

    $compiledRules = new CompiledRules($rules);

    it('must return empty if scope does not found', function (CompiledRules $compiledRules)  {
        expect($compiledRules->queryRule('not_found_scope', '', ''))->toBeEmpty();
    })->with([$compiledRules]);

    it('must return empty if resource does not found', function (CompiledRules $compiledRules) {
        expect($compiledRules->queryRule('scope1', 'not_found_resource', ''))->toBeEmpty();
    })->with([$compiledRules]);

    it('must return empty if action does not found', function (CompiledRules $compiledRules) {
        expect($compiledRules->queryRule('scope1', 'resource1', 'delete'))->toBeEmpty();
    })->with([$compiledRules]);

    it('must return all rule when action is unspecified', function (CompiledRules $compiledRules) {
        expect(
            array_map(
                fn (Rule $item) => "$item",
                $compiledRules->queryRule('scope1', 'resource1', '')
            )
        )->toEqual([
            'scope1:resource1/666:*',
            'scope1:resource1/5:read',
            'scope1:resource1/[10,11,12]:read',
            'scope1:resource1/{"expired":true}:read'
        ]);
    })->with([$compiledRules]);

    it('must return expected rule id', function (CompiledRules $compiledRules) {
        $rules1 = $compiledRules->queryRule('scope1', 'resource1', 'read');
        expect(array_map(fn (Rule $rule) => $rule->getRuleId(), $rules1))
            ->toHaveCount(3)
            ->toContain(
            2, 3, 4
            );

        $rules2 = $compiledRules->queryRule('scope1', 'resource1', '*');
        expect(array_map(fn (Rule $rule) => $rule->getRuleId(), $rules2))
            ->toHaveCount(1)
            ->toContain(1);


        $rules3 = $compiledRules->queryRule('scope2', 'resource1', 'read');
        expect(array_map(fn (Rule $rule) => $rule->getRuleId(), $rules3))
            ->toHaveCount(2)
            ->toContain(5, 7);
    })->with([$compiledRules]);

    it('must return all rules inside the scope whatever resource and actions', function(CompiledRules $compiledRules) {
        $rules = $compiledRules->queryRule('scope1', '', '');
        expect(array_map(fn (Rule $rule) => $rule->getRuleId(), $rules))
            ->toHaveCount(5)
            ->toContain(1, 2, 3, 4, 6);
    })->with([$compiledRules]);

    it('must return all rules inside the scope with specific action and whatever resource', function(CompiledRules $compiledRules) {
        $rules = $compiledRules->queryRule('scope1', '', 'update');
        expect(array_map(fn (Rule $rule) => $rule->getRuleId(), $rules))
            ->toHaveCount(2)
            ->toContain(1, 6);
    })->with([$compiledRules]);
});