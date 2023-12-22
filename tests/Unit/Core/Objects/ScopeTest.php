<?php

use Abilities\Core\Objects\Scope;

it("must throw error when passing empty/blank argument on 'scope' ", function () {
    new Scope('  ');
})->throws(
    InvalidArgumentException::class,
    'Scope must not be empty'
);

it("must throw error when passing invalid scope name", function () {
    new Scope('scope!');
})->throws(
    InvalidArgumentException::class,
    'Invalid scope naming. Please use a combination of lowercase letter, number, dash and underscore only'
);

test("Must use 'global' when using default argument", function () {
    expect((new Scope())->get())->toBe('global');
});

test("Successfully define specific action", function () {
    expect("" . new Scope('some_scope'))->toBe('some_scope');
});