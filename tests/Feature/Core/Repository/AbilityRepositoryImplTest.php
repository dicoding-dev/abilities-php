<?php

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Comparator\AbilityCheckerImpl;
use Abilities\Core\Repository\MutableUserAbilityRepository;
use Abilities\Core\Storage\StorageInterface;

describe('Get Ability Checker Function Test', function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(new StorageFixture())->makePartial();
        $this->abilityRepositoryInstance = new MutableUserAbilityRepository(
            1,
            $this->storage
        );
    });

    it('must return ability checker when rules is compiled', function () {
        expect($this->abilityRepositoryInstance->getChecker())
            ->toBeInstanceOf(AbilityCheckerImpl::class);

        $this->storage->shouldHaveReceived()
            ->onGetRulesByUserId(1);
    });

});

describe('Set the ability test', function () {
    it('can set the ability from whole action (star) to specific action', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:*',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->setAbility('read', 'resource', 'scope', 123);

        expect($storage->getRules())
            ->toEqual([
                2 => 'scope:resource/4:*',
                3 => 'scope:resource/123:read'
            ]);
    });

    it('can set the ability from specific action to whole action (star) action', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/123:update',
                3 => 'scope:resource/4:*'
            ])
        );

        $repository->setAbility('*', 'resource', 'scope', 123, );

        expect($storage->getRules())
            ->toEqual([
                3 => 'scope:resource/4:*',
                4 => 'scope:resource/123:*'
            ]);
    });

    it('must add new ability when the scope, resource and field attributes is matched, but not match with the action', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->setAbility('update', 'resource', 'scope', 123);

        expect($storage->getRules())
            ->toEqual([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*',
                3 => 'scope:resource/123:update',
            ]);
    });

    it('must add new ability when the field attributes is not match', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->setAbility('*', 'resource', 'scope', 5);

        expect($storage->getRules())
            ->toEqual([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*',
                3 => 'scope:resource/5:*',
            ]);
    });

    it('can invert the matched rules, when it is not inverted yet', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->setAbility('read', 'resource', 'scope', 123, true);

        expect($storage->getRules())
            ->toEqual([
                2 => 'scope:resource/4:*',
                3 => '!scope:resource/123:read',
            ]);
    });

    test('when the rule is not updated', function() {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->setAbility('read', 'resource', 'scope', 123);

        expect($storage->getRules())
            ->toEqual([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ]);
    });
});


describe("Unset the ability test", function () {
    it('must delete the ability when the rule is matched exactly', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->unsetAbility('read', 'resource', 'scope', 123);

        expect($storage->getRules())
            ->toEqual([
                2 => 'scope:resource/4:*'
            ]);
    });

    it('must not remove the ability when the rule is unmatched', function () {
        $repository = new MutableUserAbilityRepository(
            1,
            $storage = new StorageFixture([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ])
        );

        $repository->unsetAbility('*', 'resource', 'scope', 123);

        expect($storage->getRules())
            ->toEqual([
                1 => 'scope:resource/123:read',
                2 => 'scope:resource/4:*'
            ]);
    });
});


class StorageFixture implements StorageInterface
{
    public function __construct(
        private array $rules = [
            1 => 'scope1:resource1:read',
            2 => 'scope1:resource2:update'
        ]
    ) {
    }

    /**
     * @inheritDoc
     */
    public function onInsertNewRule(int|string $userIdentification, string $newRule): int
    {
        $this->rules[$id = array_key_last($this->rules)+1] = $newRule;
        return $id;
    }

    /**
     * @inheritDoc
     */
    public function onUpdateRule(int $ruleId, int|string $userId, string $updatedRule): void
    {
        $this->rules[$ruleId] = $updatedRule;
    }

    /**
     * @inheritDoc
     */
    public function onDeleteSpecificRule(int $deletedRuleId, int|string $userId): void
    {
        unset($this->rules[$deletedRuleId]);
    }

    /**
     * @inheritDoc
     */
    public function onGetRulesByUserId(int|string $userIdentification): array
    {
        $mappedObjectRules = [];
        foreach ($this->rules as $index=>$rule) {
            $mappedObjectRules[] = (object) [
                'id' => $index,
                'rule' => $rule
            ];
        }

        return $mappedObjectRules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
