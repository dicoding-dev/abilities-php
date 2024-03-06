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
    expect((new Action())->get())->toBe('*');
});

test("Successfully define specific action", function () {
    expect("" . new Action('read'))->toBe('read');
});

it('can know if the action is whole action (star)', function () {
    expect((new Action('*'))->wholeAction())->toBeTrue();

});

describe('match() function test', function () {
   it('must return true when the current action is whole action', function () {
       $currentAction = new Action();

       expect($currentAction->match('create'))->toBeTrue();
       expect($currentAction->match('*'))->toBeTrue();
   });

   it ('must return true when the match with specific action', function () {
       $currentAction = new Action('create');
       expect($currentAction->match('create'))->toBeTrue();
   });

   it('must return false when not match with specific action', function () {
       $currentAction = new Action('create');
       expect($currentAction->match('update'))->toBeFalse();
   });
});