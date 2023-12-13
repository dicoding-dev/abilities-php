<?php

use Abilities\Objects\Scope;

test("Must use 'global' when using default argument", function () {
    expect((new Scope())->get())->toBeString('global');
});

test("Successfully define specific action", function () {
    expect("" . new Scope('some_scope'))->toBeString('some_scope');
});