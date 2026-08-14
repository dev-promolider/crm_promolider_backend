<?php

namespace Promolider\Domain\Preferences\Contracts;

interface PreferencesRepositoryInterface
{
    /**
     * Get all preferences for a user
     * @param int $userId
     * @return array
     */
    public function getUserPreferences(int $userId): array;

    /**
     * Save an array of category IDs as preferences for a user
     * @param int $userId
     * @param array $categoryIds
     * @return void
     */
    public function savePreferences(int $userId, array $categoryIds): void;

    /**
     * Delete an array of category IDs from a user's preferences
     * @param int $userId
     * @param array $categoryIds
     * @return void
     */
    public function deletePreferences(int $userId, array $categoryIds): void;

    /**
     * Update user's preference status (e.g. status_preference = 1)
     * @param int $userId
     * @param int $status
     * @return void
     */
    public function updateUserPreferenceStatus(int $userId, int $status): void;
}
