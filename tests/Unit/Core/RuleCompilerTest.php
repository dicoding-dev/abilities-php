<?php

use Abilities\Core\Compiler\RuleCompiler;
use Abilities\Core\Exceptions\CompilerException;

describe("Compile a rule syntax", function () {

    it("must throw error if syntax is empty", function () {
        RuleCompiler::compile("");
    })->throws(
        CompilerException::class,
        'Syntax must not be empty'
    );

    it("must throw error if scope is empty", function () {
        RuleCompiler::compile(':resource:action');
    })->throws(
        CompilerException::class,
        'Scope must not empty'
    );

    it("must throw error if resource is empty", function () {
        RuleCompiler::compile('scope::action');
    })->throws(
        CompilerException::class,
        'Resource must not empty'
    );

    it("must throw error if action is empty", function () {
        RuleCompiler::compile('scope:resource:');
    })->throws(
        CompilerException::class,
        'Action must not empty'
    );

    describe("must return expected", function () {
        test('inverted rules', function () {
            $rule = RuleCompiler::compile('!scope:resource:action');

            expect($rule->getScope()->get())->toBe('scope')
                ->and($rule->getResource()->getResourceString())->toBe('resource')
                ->and($rule->getResource()->getField())->toBeNull()
                ->and($rule->getAction()->get())->toBe('action')
                ->and($rule->isInverted())->toBeTrue();
        });

        test("when resource have no field", function() {
            $rule = RuleCompiler::compile('scope:resource:action');

            expect($rule->getScope()->get())->toBe('scope')
                ->and($rule->getResource()->getResourceString())->toBe('resource')
                ->and($rule->getResource()->getField())->toBeNull()
                ->and($rule->getAction()->get())->toBe('action');
        });

        test("when resource have single field", function() {
            $rule = RuleCompiler::compile('scope:resource/some_field:action');

            expect($rule->getScope()->get())->toBe('scope')
                ->and($rule->getResource()->getResourceString())->toBe('resource')
                ->and($rule->getResource()->getField())->toBe('some_field')
                ->and($rule->getAction()->get())->toBe('action');
        });

        test("when resource have object field", function() {
            $rule = RuleCompiler::compile('scope:resource/{ "fieldA": 2, "fieldB": 5 }:action');

            expect($rule->getScope()->get())->toBe('scope')
                ->and($rule->getResource()->getResourceString())->toBe('resource')
                ->and($rule->getAction()->get())->toBe('action');

            $field = $rule->getResource()->getField();
            expect($field->fieldA)->toBeInt(2)
                ->and($field->fieldB)->toBeInt(5);
        });

        test("when resource have array field", function() {
            $rule = RuleCompiler::compile('scope:resource/[1, 2, 3]:action');

            expect($rule->getScope()->get())->toBe('scope')
                ->and($rule->getResource()->getResourceString())->toBe('resource')
                ->and($rule->getAction()->get())->toBe('action');

            $field = $rule->getResource()->getField();
            expect($field)->toBe([1, 2, 3]);
        });
    });
});