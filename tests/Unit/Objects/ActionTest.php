<?php

use Abilities\Objects\Action;

it('must fail when assigned with empty value', function () {
    new Action('');
})->throws(
    InvalidArgumentException::class,
    'Action must not be empty'
);

test("Must use star symbol when using default argument", function () {
    expect((new Action())->get())->toBeString('*');
});

test("Successfully define specific action", function () {
    expect("" . new Action('read'))->toBeString('read');
});