<?php

namespace Promolider\Infrastructure\Preferences\Out\Persistence;

use App\Models\Preferences;
use App\Models\User;
use Promolider\Domain\Preferences\Contracts\PreferencesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentPreferencesRepository implements PreferencesRepositoryInterface
{
    public function getUserPreferences(int $userId): array
    {
        $myPreferences = Preferences::join('categories', 'categories.id', '=', 'preferences.categories_id')
            ->where('preferences.user_id', $userId)
            ->select('preferences.id', 'preferences.categories_id', 'categories.name', 'categories.icon')
            ->get();

        return $myPreferences->toArray();
    }

    public function savePreferences(int $userId, array $categoryIds): void
    {
        // Avoid inserting duplicates by checking existing ones
        $existing = Preferences::where('user_id', $userId)
            ->whereIn('categories_id', $categoryIds)
            ->pluck('categories_id')
            ->toArray();

        $toInsert = array_diff($categoryIds, $existing);

        if (!empty($toInsert)) {
            $insertData = [];
            $now = now();
            foreach ($toInsert as $categoryId) {
                $insertData[] = [
                    'user_id' => $userId,
                    'categories_id' => $categoryId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            Preferences::insert($insertData);
        }
    }

    public function deletePreferences(int $userId, array $categoryIds): void
    {
        Preferences::where('user_id', $userId)
            ->whereIn('categories_id', $categoryIds)
            ->delete();
    }

    public function updateUserPreferenceStatus(int $userId, int $status): void
    {
        User::where('id', $userId)->update(['status_preference' => $status]);
    }
}
