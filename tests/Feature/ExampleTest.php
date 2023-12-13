<?php

use Abilities\Test;

test('example', function () {
    expect(true)->toBeTrue();
});

describe("Some describe test", function () {
    test("Testing Test", function () {
        expect(Test::formatString('ini_string'))->toBeString('format:ini_string');
    });
    test('some example again', function () {
        expect(Test::a())->toBeTrue();

    });
});