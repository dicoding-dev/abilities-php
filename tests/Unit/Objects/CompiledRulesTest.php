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
});