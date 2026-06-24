<?php

namespace App\Services;

use App\Models\QuestionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class QuestionCategoryService
{
    public function list(array $filters = []): Collection
    {
        return QuestionCategory::query()
            ->when($filters['search'] ?? null, function ($query, $term) {
                $query->where(function ($innerQuery) use ($term) {
                    $innerQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['is_active']);
            })
            ->orderByDesc('updated_at')
            ->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return QuestionCategory::query()
            ->when($filters['search'] ?? null, function ($query, $term) {
                $query->where(function ($innerQuery) use ($term) {
                    $innerQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): QuestionCategory
    {
        return $this->persist(new QuestionCategory(), $data);
    }

    public function update(QuestionCategory $category, array $data): QuestionCategory
    {
        return $this->persist($category, $data);
    }

    public function findOrFail(int $id): QuestionCategory
    {
        return QuestionCategory::query()->findOrFail($id);
    }

    public function toggleStatus(QuestionCategory $category): QuestionCategory
    {
        $category->update(['is_active' => !$category->is_active, 'updated_by' => $this->currentUserId()]);
        return $category->refresh();
    }

    protected function persist(QuestionCategory $category, array $data): QuestionCategory
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'questions_count' => $data['questions_count'] ?? $category->questions_count ?? 0,
        ];

        if (! $category->exists) {
            $payload['created_by'] = $this->currentUserId();
        }

        $payload['updated_by'] = $this->currentUserId();

        $category->fill($payload);
        $category->save();

        return $category->fresh(['creator', 'updater']);
    }

    protected function currentUserId(): ?int
    {
        try {
            return Auth::id();
        } catch (\Throwable $th) {
            Log::warning('QuestionCategoryService: Auth id unavailable', [
                'error' => $th->getMessage(),
            ]);
            return null;
        }
    }
}
