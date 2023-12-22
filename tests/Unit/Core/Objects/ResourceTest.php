<?php

use Abilities\Core\Objects\Resource;

it("must throw error when passing empty/blank argument on 'resource' ", function () {
    new Resource('  ');
})->throws(
    InvalidArgumentException::class,
    'Resource must not be empty'
);

it("must throw error when passing invalid scope name", function () {
    new Resource('resource!');
})->throws(
    InvalidArgumentException::class,
    'Invalid resource naming. Please use a combination of lowercase letter, number, dash and underscore only'
);

describe("toString function test", function () {
    test("successfully encode without field", function () {
        expect("" . new Resource("some_resource"))->toBeString('some_resource');
    });
    
    test("successfully encode with array field", function () {
        expect("" . new Resource("some_resource", [1, 2]))
            ->toBeString('some_resource/[1,2]');
    });

    test("successfully encode with json object field", function () {
        expect("" . new Resource(
            "some_resource",
                (object) [
                    'some_field' => 'some_value'
                ]
            )
        )->toBeString('some_resource/{"some_field":"some_value"}');
    });

    test("successfully encode with non json field", function () {
        expect("" . new Resource("some_resource", 'non_json_field'))
            ->toBeString('some_resource/non_json_field');
    });
});

describe("allField() function test", function () {
    it('must return true when field property is empty', function () {
        expect(
            (new Resource("some_resource"))->allField()
        )->toBeTrue();
    });

    it('must return true when field property is star', function () {
        expect(
            (new Resource("some_resource", '*'))->allField()
        )->toBeTrue();
    });

    it('must return false when field property is neither star and empty', function () {
        expect(
            (new Resource("some_resource", 'some_field'))->allField()
        )->toBeFalse();
    });
});


/**
 * matchField() Precedence
 *  1. Star-<field> rules or Empty-<field> rules         --> jobs:vacancies/*:update or jobs:vacancies:update
 *  2. Single-<field> rules                              --> jobs:vacancies/2:update
 *  3. Array-<field> rules (OR method)                   --> jobs:vacancies/[4, 5, 6]:update
 *  4. Object-<field> rules (AND method per attributes)  --> jobs:vacancies/{"authoredBy": 22}:update
 */

describe('matchField() function test', function () {
    it('must return true if the resource rule has star field', function () {
        expect(
            (new Resource('some_resource'))->matchField('some_field')
        )->toBeTrue();
    });

    it('must return false if the argument is empty', function () {
        expect(
            (new Resource('some_resource', 'some_field'))->matchField(null)
        )->toBeFalse();
    });

    it('must return true if match the single field ', function () {
        expect(
            (new Resource('some_resource', 'some_field'))->matchField('some_field')
        )->toBeTrue();
    });

    it('must return false if fields does not contains the field argument ', function () {
        expect(
            (new Resource('some_resource', [1, 2, 3, 4, 5]))->matchField([4, 10, 3])
        )->toBeFalse();
    });

    it('must return true if fields contains the field argument ', function () {
        expect(
            (new Resource('some_resource', [1, 2, 3, 4, 5]))->matchField([2, 4])
        )->toBeTrue();
    });

    it('must return true if fields contains exactly one field argument ', function () {
        expect(
            (new Resource('some_resource', [1, 2, 3, 4, 5]))->matchField(5)
        )->toBeTrue();
    });

    it('must return true if field object match exactly with argument', function () {
        expect(
            (new Resource('some_resource', (object) ['author' => 'John', 'age' => '5']))
                ->matchField((object) ['author' => 'John', 'age' => 5])
        )->toBeTrue();
    });

    it('must return false if field object doesnt match exactly with argument', function () {
        expect(
            (new Resource('some_resource', (object) ['author' => 'John', 'age' => '5']))
                ->matchField((object) ['author' => 'John', 'age' => 10])
        )->toBeFalse();
    });
});