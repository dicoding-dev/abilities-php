<?php

namespace Abilities\Core\Storage;

/**
 * An interface for supports the 'abilities' library for making its rule become persistent on the storage.
 * Recommendation Storage: SQL-like storage
 * Table column :
 * id (bigint), user_id (bigint), rule (text), created_at (datetime), updated_at (datetime)
 *
 * It's recommended to cache the query result.
 */
interface StorageInterface
{
    /**
     * This method called when we have a new rule inserted into database.
     *
     * @param int|string $userIdentification the user's ID
     * @param string $newRule a new rule to be inserted into database
     * @return int the ID of new rule
     */
    public function onInsertNewRule(int|string $userIdentification, string $newRule): int;

    /**
     * This method called when a single rule has been updated. Usually, this is happened when a `field`
     * inside a rule is updated.
     * @param int $ruleId the rule ID
     * @param string $updatedRule the updated rule for the current rule ID
     */
    public function onUpdateRule(int $ruleId, int|string $userId, string $updatedRule): void;

    /**
     * This method called when a single rule is deleted.
     *
     * @param int $deletedRuleId the rule defined by ID to be deleted
     */
    public function onDeleteSpecificRule(int $deletedRuleId, int|string $userId): void;

    /**
     * This method called at initial, or any changes made after 'commit()' called.
     * It will retrieve all rules by specific @param int|string $userIdentification.
     *
     * @return list<object{
     *     id: int,
     *     rule: string
     * }> the list of rules that owned by user
     */
    public function onGetRulesByUserId(int|string $userIdentification): array;
}
