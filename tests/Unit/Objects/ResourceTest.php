<?php

use Abilities\Objects\Resource;

it("must throw error when passing empty/blank argument on 'resource' ", function () {
    new Resource('  ');
})->throws(
    InvalidArgumentException::class,
    'Resource must not be empty'
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
                [
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