<?php

namespace App\Services;

use App\Models\QuestionCategory;
use App\Models\QuestionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionItemService
{
    public function create(QuestionCategory $category, array $data): QuestionItem
    {
        return DB::transaction(function () use ($category, $data) {
            $question = new QuestionItem();
            $question->fill([
                'title' => $data['title'],
                'body' => $data['body'] ?? null,
                'status' => $data['status'],
                'difficulty' => $data['difficulty'],
                'time_limit' => $data['time_limit'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'meta' => $data['meta'] ?? null,
            ]);
            $question->question_category_id = $category->id;
            $question->created_by = $this->currentUserId();
            $question->updated_by = $this->currentUserId();
            $question->save();

            foreach ($data['options'] as $index => $option) {
                $question->options()->create([
                    'label' => $this->buildOptionLabel($index),
                    'text' => $option['text'],
                    'is_correct' => (bool) $option['is_correct'],
                    'position' => $index + 1,
                ]);
            }

            $category->increment('questions_count');

            return $question->fresh('options');
        });
    }

    public function update(QuestionItem $question, array $data): QuestionItem
    {
        return DB::transaction(function () use ($question, $data) {
            $question->fill([
                'title' => $data['title'],
                'body' => $data['body'] ?? null,
                'status' => $data['status'],
                'difficulty' => $data['difficulty'],
                'time_limit' => $data['time_limit'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'meta' => $data['meta'] ?? null,
            ]);
            $question->updated_by = $this->currentUserId();
            $question->save();

            $question->options()->delete();

            foreach ($data['options'] as $index => $option) {
                $question->options()->create([
                    'label' => $this->buildOptionLabel($index),
                    'text' => $option['text'],
                    'is_correct' => (bool) $option['is_correct'],
                    'position' => $index + 1,
                ]);
            }

            return $question->fresh('options');
        });
    }

    public function delete(QuestionCategory $category, QuestionItem $question): void
    {
        DB::transaction(function () use ($category, $question) {
            $question->delete();

            if ($category->questions_count > 0) {
                $category->decrement('questions_count');
            }
        });
    }

    public function previewForCategory(QuestionCategory $category, ?int $limit = null): Collection
    {
        $query = $category->questions()
            ->with(['options' => function ($query) {
                $query->orderBy('position');
            }])
            ->orderBy('created_at');

        if ($limit) {
            $query->take($limit);
        }

        return $query->get();
    }

    protected function buildOptionLabel(int $index): string
    {
        $alphabet = range('A', 'Z');
        return $alphabet[$index] ?? ('OP-' . ($index + 1));
    }

    protected function currentUserId(): ?int
    {
        try {
            return Auth::id();
        } catch (\Throwable $th) {
            Log::warning('QuestionItemService: Auth id unavailable', [
                'error' => $th->getMessage(),
            ]);
            return null;
        }
    }
}
