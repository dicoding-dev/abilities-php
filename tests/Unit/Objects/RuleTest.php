<?php

use Abilities\Objects\Action;
use Abilities\Objects\Resource;
use Abilities\Objects\Rule;
use Abilities\Objects\Scope;

function makeRule(
    Resource $resource = new Resource('resource'),
    bool $isInverted = false
): Rule {
    return new Rule(
        new Scope('scope'),
        $resource,
        new Action('action'),
        $isInverted
    );
};

describe("getRuleId function test", function () {
    it("must throw error when not calling setRuleId beforehand", function () {
        makeRule()->getRuleId();
    })->throws(
        Exception::class,
        "Don't forget to call 'setRuleId()' before get the rule ID"
    );

    it("returns expected ruleId", function () {
        $rule = makeRule();
        $rule->setRuleId(1);

        expect($rule->getRuleId())->toBeOne();
    });
});

describe("toString function test", function () {
    test("successfully encode inverted rules", function () {
        $rule = makeRule(isInverted: true);
        expect("$rule")->toEqual("!scope:resource/*:action");
    });
    test("successfully encode normal rules", function () {
        $rule = makeRule();
        expect("$rule")->toEqual("scope:resource/*:action");
    });
    test("successfully rule with custom resource", function () {
        $rule = makeRule(
            resource: new Resource(
                'res',
                (object) [
                    'param_1' => 22
                ]
            )
        );
        expect("$rule")->toEqual("scope:res/{\"param_1\":22}:action");
    });
});