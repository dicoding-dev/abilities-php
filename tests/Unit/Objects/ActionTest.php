<?php

use Abilities\Objects\Action;

test("Must use star symbol when using default argument", function () {
    expect((new Action())->get())->toBeString('*');
});

test("Successfully define specific action", function () {
    expect("" . new Action('read'))->toBeString('read');
});