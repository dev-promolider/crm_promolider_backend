<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\EditTemplate;
use Promolider\Domain\Marketing\Entities\EditablePage;
use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EloquentEditablePageRepository implements EditablePageRepositoryInterface
{
    private function mapToEntity(?EditTemplate $model): ?EditablePage
    {
        if (!$model) return null;

        return new EditablePage(
            id: $model->id,
            userId: $model->user_id,
            templateId: $model->template_id,
            title: $model->title,
            contentHtml: $model->content_html,
            editedFields: $model->edited_fields,
            status: $model->status,
            slug: $model->slug,
            publicUrl: ($model->status === 'published' && $model->slug)
                ? url("/pages/{$model->slug}")
                : null,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    private function modelToArray(EditTemplate $model): array
    {
        $data = $model->toArray();
        $data['public_url'] = ($model->status === 'published' && $model->slug)
            ? url("/pages/{$model->slug}")
            : null;
        return $data;
    }

    public function getAll(): array
    {
        return EditTemplate::with(['user', 'template'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($m) => $this->modelToArray($m))
            ->toArray();
    }

    public function getByUser(int $userId): array
    {
        return EditTemplate::where('user_id', $userId)
            ->with(['template'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($m) => $this->modelToArray($m))
            ->toArray();
    }

    public function getById(int $id): ?EditablePage
    {
        $model = EditTemplate::with(['user', 'template'])->find($id);
        return $this->mapToEntity($model);
    }

    public function getPublicBySlug(string $slug): ?EditablePage
    {
        $model = EditTemplate::where('slug', $slug)
            ->where('status', 'published')
            ->with(['user', 'template'])
            ->first();

        return $this->mapToEntity($model);
    }

    public function create(array $data): EditablePage
    {
        $data['status'] ??= 'draft';

        // Auto-generate slug if publishing
        if ($data['status'] === 'published' && empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        $model = EditTemplate::create($data);
        $model->load(['user', 'template']);

        return $this->mapToEntity($model);
    }

    public function update(int $id, array $data): ?EditablePage
    {
        $model = EditTemplate::find($id);
        if (!$model) return null;

        // Auto-generate slug if publishing and slug is missing
        if (!empty($data['status']) && $data['status'] === 'published' && empty($model->slug) && empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'] ?? $model->title, $id);
        }

        $model->update($data);
        $model->refresh();
        $model->load(['user', 'template']);

        return $this->mapToEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = EditTemplate::find($id);
        if (!$model) return false;
        return (bool) $model->delete();
    }

    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        $query = EditTemplate::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            $query = EditTemplate::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
