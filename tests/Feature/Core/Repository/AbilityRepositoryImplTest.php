<?php

use Abilities\Core\Comparator\AbilityChecker;
use Abilities\Core\Comparator\AbilityCheckerImpl;
use Abilities\Core\Repository\MutableUserAbilityRepository;
use Abilities\Core\Storage\StorageInterface;

describe('Get Ability Checker Function Test', function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
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

describe('Add ability test', function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new MutableUserAbilityRepository(
            1,
            $this->storage
        );
    });

    it('must not add the ability when the user has the rule', function () {
        $this->abilityRepositoryInstance->addAbility(
            'read', 'resource1', 'scope1'
        );

        $this->storage->shouldNotHaveReceived()
            ->onInsertNewRule(1, andAnyOthers());
    });

    it('must add the ability when the user doesnt have the rule', function () {
        $this->abilityRepositoryInstance->addAbility(
            'update', 'resource2', 'scope1'
        );

        expect($this->abilityRepositoryInstance->getChecker()->hasExactRule('scope1:resource2:update'))
            ->toBeTrue();
    });

    it('can add many rules', function () {
        $this->abilityRepositoryInstance->addAbilities([
            'scope2:resource1/5:read',
            'scope2:resource1/6:update'
        ]);

        expect($this->abilityRepositoryInstance->getChecker()->hasExactRule('scope2:resource1/5:read'))
            ->toBeTrue()
            ->and($this->abilityRepositoryInstance->getChecker()->hasExactRule('scope2:resource1/6:update'))
            ->toBeTrue();
    });
});

describe("Remove the ability test", function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new MutableUserAbilityRepository(
            1,
            $this->storage
        );
    });

    it('cannot delete when the ability doesnt exist', function () {
        $this->abilityRepositoryInstance->removeAbility(
            'edit', 'resource2', 'scopeX'
        );

        $this->storage->shouldNotHaveReceived('onDeleteSpecificRule');
    });

    it('can remove the ability', function () {
        $this->abilityRepositoryInstance->removeAbility(
            'read', 'resource1', 'scope1'
        );

        /** @var AbilityChecker $checker */
        $checker = $this->abilityRepositoryInstance->getChecker();
        expect($checker->hasExactRule('scope1:resource1:read'))
            ->toBeFalse();
    });

    it('can remove many abilities', function () {
        $this->abilityRepositoryInstance->removeAbilities([
            'scope1:resource1:read',
            'scope1:resource2:update'
        ]);

        /** @var AbilityChecker $checker */
        $checker = $this->abilityRepositoryInstance->getChecker();
        expect($checker->hasExactRule('scope1:resource1:read'))
            ->toBeFalse()
            ->and($checker->hasExactRule('scope1:resource2:update'))
            ->toBeFalse();
    });
});

describe("Update the ability rule test", function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new MutableUserAbilityRepository(
            1,
            $this->storage
        );
    });


    it('cannot update the ability when the ability doesnt exist', function () {
        $this->abilityRepositoryInstance->update(
            'scopeX:resource2:edit',
            'scopeX:resource2/5:edit'
        );

        $this->storage->shouldNotHaveReceived('onUpdateRule');
    });

    it('can update the ability', function () {
        $this->abilityRepositoryInstance->update(
            'scope1:resource1:read',
            'scope1:resource1/666:read'
        );

        /** @var AbilityChecker $checker */
        $checker = $this->abilityRepositoryInstance->getChecker();
        expect($checker->hasExactRule('scope1:resource1:read'))
            ->toBeFalse()
            ->and($checker->hasExactRule('scope1:resource1/666:read'))
            ->toBeTrue();
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
