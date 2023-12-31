<?php

use Abilities\Core\Objects\Resource;

it("must throw error when passing empty/blank argument on 'resource' ", function () {
    new Resource('  ');
})->throws(
    InvalidArgumentException::class,
    'Resource must not be empty'
);

it("must throw error when passing invalid resource name", function () {
    new Resource('resource!');
})->throws(
    InvalidArgumentException::class,
    'Invalid resource naming. Please use a combination of lowercase letter, number, dash and underscore only'
);

describe("toString function test", function () {
    test("successfully encode without field", function () {
        expect("" . new Resource("some_resource"))->toBe('some_resource/*');
    });
    
    test("successfully encode with array field", function () {
        expect("" . new Resource("some_resource", [1, 2]))
            ->toBe('some_resource/[1,2]');
    });

    test("successfully encode with json object field", function () {
        expect("" . new Resource(
            "some_resource",
                (object) [
                    'some_field' => 'some_value'
                ]
            )
        )->toBe('some_resource/{"some_field":"some_value"}');
    });

    test("successfully encode with non json field", function () {
        expect("" . new Resource("some_resource", 'non_json_field'))
            ->toBe('some_resource/non_json_field');
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

describe('isEqualWith() function test', function () {
    it('must return false when resource name is different', function () {
        $base = new Resource('some_resource');
        $compared = new Resource('some_resource2');

        expect($base->isEqualWith($compared))
            ->toBeFalse();
    });

    it('must return false when resource field type is different', function () {
        $base = new Resource('some_resource', 5);
        $compared = new Resource('some_resource', [5, 6]);

        expect($base->isEqualWith($compared))
            ->toBeFalse();
    });

    it('must return true when both resources have star field', function () {
        $base = new Resource('some_resource', '*');
        $compared = new Resource('some_resource', '*');

        expect($base->isEqualWith($compared))
            ->toBeTrue();
    });

    it('must return false when both resources have different singular field', function () {
        $base = new Resource('some_resource', 6);
        $compared = new Resource('some_resource', 7);

        expect($base->isEqualWith($compared))
            ->toBeFalse();
    });

    it('must return true when both resources have same singular field value', function () {
        $base = new Resource('some_resource', 7);
        $compared = new Resource('some_resource', '7');

        expect($base->isEqualWith($compared))
            ->toBeTrue();
    });

    it('must return false when both resources have different array field', function () {
        $base = new Resource('some_resource', [6, 7, 8]);
        $compared = new Resource('some_resource', [7, 5, 6]);

        expect($base->isEqualWith($compared))
            ->toBeFalse();
    });

    it('must return true when both resources have same array field', function () {
        $base = new Resource('some_resource', [6, 7, 8]);
        $compared = new Resource('some_resource', [7, 8, 6]);

        expect($base->isEqualWith($compared))
            ->toBeTrue();
    });

    it('must return false when both resources have different object field', function () {
        $base = new Resource('some_resource', (object)[
            'a' => 1,
            'b' => 2,
            'c' => 3
        ]);
        $compared = new Resource('some_resource', (object)[
            'a' => 1,
            'b' => 2
        ]);

        expect($base->isEqualWith($compared))
            ->toBeFalse();
    });

    it('must return true when both resources have same object field', function () {
        $base = new Resource('some_resource', (object)[
            'a' => 1,
            'b' => 2,
            'c' => 3
        ]);
        $compared = new Resource('some_resource', (object)[
            'b' => 2,
            'c' => 3,
            'a' => 1
        ]);

        expect($base->isEqualWith($compared))
            ->toBeTrue();
    });
});