<?php

use Abilities\Objects\CompiledRules;
use Abilities\Objects\Rule;

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
        $rules = $compiledRules->queryRule('scope1', 'resource1', 'read');

        expect(array_map(fn (Rule $rule) => $rule->getRuleId(), $rules))->toContain(
            2, 3, 4
        );
    })->with([$compiledRules]);
});