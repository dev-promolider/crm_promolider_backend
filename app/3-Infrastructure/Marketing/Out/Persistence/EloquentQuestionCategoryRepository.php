<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\QuestionCategory as EloquentQuestionCategory;
use App\Models\QuestionItem as EloquentQuestionItem;
use App\Models\QuestionItemOption as EloquentQuestionItemOption;
use Promolider\Domain\Marketing\Entities\QuestionCategory;
use Promolider\Domain\Marketing\Entities\QuestionItem;
use Promolider\Domain\Marketing\Entities\QuestionItemOption;
use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class EloquentQuestionCategoryRepository implements QuestionCategoryRepositoryInterface
{
    // ─── Question Categories ───────────────────────────────────────────────

    public function list(array $filters = []): array
    {
        $query = EloquentQuestionCategory::query()
            ->with(['creator', 'updater'])
            ->orderBy('updated_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->get()->map(fn($m) => $this->toEntity($m)->toArray())->values()->all();
    }

    public function paginate(array $filters = [], int $perPage = 15): array
    {
        $query = EloquentQuestionCategory::query()
            ->with(['creator', 'updater'])
            ->orderBy('updated_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $paginator = $query->paginate($perPage);
        $items = $paginator->items();

        return [
            'data' => array_map(fn($m) => $this->toEntity($m)->toArray(), $items),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    public function findById(int $id): ?QuestionCategory
    {
        $model = EloquentQuestionCategory::with(['creator', 'updater'])->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?QuestionCategory
    {
        $model = EloquentQuestionCategory::with(['creator', 'updater'])->where('slug', $slug)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): QuestionCategory
    {
        $model = EloquentQuestionCategory::create($data);
        $model->load(['creator', 'updater']);
        return $this->toEntity($model);
    }

    public function update(int $id, array $data): QuestionCategory
    {
        $model = EloquentQuestionCategory::findOrFail($id);
        $model->update($data);
        $model->load(['creator', 'updater']);
        return $this->toEntity($model);
    }

    public function toggleStatus(int $id): QuestionCategory
    {
        $model = EloquentQuestionCategory::findOrFail($id);
        $model->update([
            'is_active' => !$model->is_active,
        ]);
        $model->load(['creator', 'updater']);
        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = EloquentQuestionCategory::find($id);
        if (!$model) {
            return false;
        }
        // Delete related questions and options
        $model->questions()->each(function ($question) {
            $question->options()->delete();
            $question->delete();
        });
        $model->delete();
        return true;
    }

    // ─── Question Items ────────────────────────────────────────────────────

    public function getQuestionsByCategory(int $categoryId, array $filters = []): array
    {
        $query = EloquentQuestionItem::with('options')
            ->where('question_category_id', $categoryId)
            ->orderBy('created_at', 'desc');

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }

        return $query->get()->map(fn($m) => $this->toQuestionEntity($m)->toArray())->values()->all();
    }

    public function findQuestionById(int $id): ?QuestionItem
    {
        $model = EloquentQuestionItem::with('options')->find($id);
        return $model ? $this->toQuestionEntity($model) : null;
    }

    public function createQuestion(int $categoryId, array $data): QuestionItem
    {
        $data['question_category_id'] = $categoryId;
        $model = EloquentQuestionItem::create($data);
        $model->load('options');
        return $this->toQuestionEntity($model);
    }

    public function createOption(array $data): QuestionItemOption
    {
        $model = EloquentQuestionItemOption::create($data);
        return $this->toOptionEntity($model);
    }

    public function updateQuestion(int $id, array $data, array $options): QuestionItem
    {
        $model = EloquentQuestionItem::findOrFail($id);

        // Only update fields that are provided
        $updateData = [];
        foreach (['title', 'body', 'status', 'difficulty', 'time_limit', 'is_active', 'updated_by'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $model->update($updateData);
        }

        // Replace options: delete existing, create new
        if (!empty($options)) {
            $model->options()->delete();
            foreach ($options as $optData) {
                $optData['question_item_id'] = $model->id;
                EloquentQuestionItemOption::create($optData);
            }
        }

        $model->load('options');
        return $this->toQuestionEntity($model);
    }

    public function deleteQuestion(int $id): bool
    {
        $model = EloquentQuestionItem::find($id);
        if (!$model) {
            return false;
        }
        $model->options()->delete();
        $model->delete();
        return true;
    }

    public function incrementQuestionsCount(int $categoryId): void
    {
        EloquentQuestionCategory::where('id', $categoryId)->increment('questions_count');
    }

    public function decrementQuestionsCount(int $categoryId): void
    {
        EloquentQuestionCategory::where('id', $categoryId)
            ->where('questions_count', '>', 0)
            ->decrement('questions_count');
    }

    // ─── Mappers ───────────────────────────────────────────────────────────

    private function toEntity(EloquentQuestionCategory $model): QuestionCategory
    {
        return new QuestionCategory(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            isActive: (bool) $model->is_active,
            questionsCount: (int) $model->questions_count,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
        );
    }

    private function toQuestionEntity(EloquentQuestionItem $model): QuestionItem
    {
        return new QuestionItem(
            id: $model->id,
            questionCategoryId: $model->question_category_id,
            title: $model->title,
            body: $model->body,
            status: $model->status,
            difficulty: $model->difficulty,
            timeLimit: $model->time_limit,
            isActive: (bool) $model->is_active,
            meta: $model->meta,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
            options: $model->relationLoaded('options')
                ? $model->options->map(fn($o) => $this->toOptionEntity($o))->toArray()
                : [],
        );
    }

    private function toOptionEntity(EloquentQuestionItemOption $model): QuestionItemOption
    {
        return new QuestionItemOption(
            id: $model->id,
            questionItemId: $model->question_item_id,
            label: $model->label,
            text: $model->text,
            isCorrect: (bool) $model->is_correct,
            position: (int) $model->position,
        );
    }
}
