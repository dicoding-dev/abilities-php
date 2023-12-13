<?php

use Abilities\Test;

test('example', function () {
    expect(true)->toBeTrue();
});

test('some example again', function () {
    expect(Test::a())->toBeTrue();

});
