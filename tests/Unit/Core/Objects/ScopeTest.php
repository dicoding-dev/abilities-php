<?php

use Abilities\Core\Objects\Scope;

it("must throw error when passing empty/blank argument on 'scope' ", function () {
    new Scope('  ');
})->throws(
    InvalidArgumentException::class,
    'Scope must not be empty'
);

test("Must use 'global' when using default argument", function () {
    expect((new Scope())->get())->toBeString('global');
});

test("Successfully define specific action", function () {
    expect("" . new Scope('some_scope'))->toBeString('some_scope');
});