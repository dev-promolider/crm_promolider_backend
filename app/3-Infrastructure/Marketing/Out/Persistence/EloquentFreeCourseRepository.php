<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\Course as EloquentCourse;
use Promolider\Domain\Marketing\Entities\FreeCourse;
use Promolider\Domain\Marketing\Ports\Out\FreeCourseRepositoryInterface;

class EloquentFreeCourseRepository implements FreeCourseRepositoryInterface
{
    public function list(array $filters = []): array
    {
        $query = EloquentCourse::query()
            ->leftJoin('categories', 'courses.id_categories', '=', 'categories.id')
            ->select('courses.*', 'categories.name as category_name')
            ->whereNull('courses.deleted_at')
            ->orderBy('courses.created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('courses.title', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('courses.status', $filters['status']);
        }

        return $query->get()->map(fn($m) => $this->toEntity($m))->toArray();
    }

    public function findById(int $id): ?FreeCourse
    {
        $model = EloquentCourse::leftJoin('categories', 'courses.id_categories', '=', 'categories.id')
            ->select('courses.*', 'categories.name as category_name')
            ->where('courses.id', $id)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): FreeCourse
    {
        $model = EloquentCourse::create([
            'title' => $data['course_name'],
            'id_categories' => $data['category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        if ($model->id_categories) {
            $model->load('category');
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = EloquentCourse::find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    private function toEntity($model): FreeCourse
    {
        return new FreeCourse(
            id: $model->id,
            courseName: $model->title ?? '',
            categoryId: $model->id_categories ?? $model->category_id ?? null,
            categoryName: $model->category_name ?? null,
            description: $model->description ?? null,
            status: $model->status,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
        );
    }
}
