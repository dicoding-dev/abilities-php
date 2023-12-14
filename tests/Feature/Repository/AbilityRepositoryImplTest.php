<?php

use Abilities\Core\AbilityChecker;
use Abilities\Core\AbilityCheckerImpl;
use Abilities\Repository\AbilityRepositoryImpl;
use Abilities\Storage\StorageInterface;

describe('Get Ability Checker Function Test', function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new AbilityRepositoryImpl(
            $this->storage
        );
    });

    it('must return error when rules not compiled yet', function () {
        $this->abilityRepositoryInstance->getChecker();
    })->throws(
        Exception::class,
        "Rules still not compiled yet. Please call setUserId() to compile rules for specific user"
    );

    it('must return ability checker when rules is compiled', function () {

        $this->abilityRepositoryInstance->setUserId(1);

        expect($this->abilityRepositoryInstance->getChecker())
            ->toBeInstanceOf(AbilityCheckerImpl::class);

        $this->storage->shouldHaveReceived()
            ->onGetRulesByUserId(1);
    });

});

describe('Add ability test', function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new AbilityRepositoryImpl(
            $this->storage
        );

        $this->abilityRepositoryInstance->setUserId(1);
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

        expect($this->abilityRepositoryInstance->getChecker()->hasRule('scope1:resource2:update'))
            ->toBeTrue();
    });

    it('can add many rules', function () {
        $this->abilityRepositoryInstance->addAbilities([
            'scope2:resource1/5:read',
            'scope2:resource1/6:update'
        ]);

        expect($this->abilityRepositoryInstance->getChecker()->hasRule('scope2:resource1/5:read'))
            ->toBeTrue()
            ->and($this->abilityRepositoryInstance->getChecker()->hasRule('scope2:resource1/6:update'))
            ->toBeTrue();
    });
});

describe("Remove the ability test", function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new AbilityRepositoryImpl(
            $this->storage
        );

        $this->abilityRepositoryInstance->setUserId(1);
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
        expect($checker->hasRule('scope1:resource1:read'))
            ->toBeFalse();
    });

    it('can remove many abilities', function () {
        $this->abilityRepositoryInstance->removeAbilities([
            'scope1:resource1:read',
            'scope1:resource2:update'
        ]);

        /** @var AbilityChecker $checker */
        $checker = $this->abilityRepositoryInstance->getChecker();
        expect($checker->hasRule('scope1:resource1:read'))
            ->toBeFalse()
            ->and($checker->hasRule('scope1:resource2:update'))
            ->toBeFalse();
    });
});

describe("Update the ability rule test", function () {
    beforeEach(function () {

        $this->storage = Mockery::mock(StorageFixture::class)->makePartial();
        $this->abilityRepositoryInstance = new AbilityRepositoryImpl(
            $this->storage
        );

        $this->abilityRepositoryInstance->setUserId(1);
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
        expect($checker->hasRule('scope1:resource1:read'))
            ->toBeFalse()
            ->and($checker->hasRule('scope1:resource1/666:read'))
            ->toBeTrue();
    });
});


class StorageFixture implements StorageInterface
{
    private array $rules = [
        'scope1:resource1:read',
        'scope1:resource2:update'
    ];

    /**
     * @inheritDoc
     */
    public function onInsertNewRule(int|string $userIdentification, string $newRule): int
    {
        $this->rules[] = $newRule;
        return count($this->rules);
    }

    /**
     * @inheritDoc
     */
    public function onUpdateRule(int $ruleId, string $updatedRule): void
    {
        $this->rules[$ruleId - 1] = $updatedRule;
    }

    /**
     * @inheritDoc
     */
    public function onDeleteSpecificRule(int $deletedRuleId): void
    {
        unset($this->rules[$deletedRuleId-1]);
    }

    /**
     * @inheritDoc
     */
    public function onGetRulesByUserId(int|string $userIdentification): array
    {
        $mappedObjectRules = [];
        foreach ($this->rules as $index=>$rule) {
            $mappedObjectRules[] = (object) [
                'id' => $index+1,
                'rule' => $rule
            ];
        }

        return $mappedObjectRules;
    }
}
