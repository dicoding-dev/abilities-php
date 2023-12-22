<?php

use Abilities\Core\Objects\Action;

it('must fail when assigned with empty value', function () {
    new Action(' ');
})->throws(
    InvalidArgumentException::class,
    'Action must not be empty'
);

it("must throw error when passing invalid action name", function () {
    new Action('read!');
})->throws(
    InvalidArgumentException::class,
    'Invalid action naming. Please use a combination of lowercase letter, number, dash and underscore only or a single star (*) character'
);

it("must throw error when passing more than one star action", function () {
    new Action('**');
})->throws(
    InvalidArgumentException::class,
    'Invalid action naming. Please use a combination of lowercase letter, number, dash and underscore only or a single star (*) character'
);

test("Must use star symbol when using default argument", function () {
    expect((new Action())->get())->toBeString('*');
});

test("Successfully define specific action", function () {
    expect("" . new Action('read'))->toBeString('read');
});