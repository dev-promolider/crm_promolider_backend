<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;
use Promolider\Domain\Marketing\Entities\Page;
use Illuminate\Support\Str;

class EloquentPageRepository implements PageRepositoryInterface
{
    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        $query = \App\Models\Template::where('name', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            $query = \App\Models\Template::where('name', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        return $slug;
    }

    private function computePublicUrl(?string $slug): ?string
    {
        if ($slug) {
            return url("/api/v1/pages/public/{$slug}");
        }
        return null;
    }

    private function mapToPage(?object $model): ?Page
    {
        if (!$model) return null;

        $slug = $model->name ? $this->generateSlug($model->name, $model->id) : null;
        $isPublished = (int) ($model->membresia ?? 0) === 1;
        $status = $isPublished ? 'published' : 'draft';

        return new Page(
            id: $model->id,
            userId: null,
            title: $model->name ?? '',
            slug: $slug,
            content: null,
            contentHtml: $model->content_html,
            stylesCss: $model->styles_css,
            thumbnail: $model->thumbnail,
            description: $model->description,
            template: null,
            editedFields: null,
            status: $status,
            type: null,
            meta: null,
            publicUrl: $isPublished ? $this->computePublicUrl($slug) : null,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function getTemplates(): array
    {
        $templates = \App\Models\Template::orderBy('name')->get();
        return $templates->map(fn($t) => $this->mapToPage($t))->toArray();
    }

    public function getUserPages(int $userId): array
    {
        $models = \App\Models\Template::orderBy('updated_at', 'desc')->get();
        return $models->map(fn($m) => $this->mapToPage($m))->toArray();
    }

    public function getPage(int $pageId): ?Page
    {
        $model = \App\Models\Template::find($pageId);
        return $this->mapToPage($model);
    }

    public function getPublicPage(string $slug): ?Page
    {
        $model = \App\Models\Template::get()->first(fn($t) => Str::slug($t->name) === $slug);
        return $this->mapToPage($model);
    }

    public function createPage(array $data): Page
    {
        $data['name'] ??= $data['title'] ?? 'Página sin título';
        $data['membresia'] ??= 0;
        $model = \App\Models\Template::create($data);
        return $this->mapToPage($model);
    }

    public function updatePage(int $pageId, array $data): ?Page
    {
        $model = \App\Models\Template::find($pageId);
        if (!$model) return null;
        $model->update($data);
        $model->refresh();
        return $this->mapToPage($model);
    }

    public function deletePage(int $pageId): bool
    {
        return (bool) \App\Models\Template::where('id', $pageId)->delete();
    }

    public function publishPage(int $pageId): ?Page
    {
        $model = \App\Models\Template::find($pageId);
        if (!$model) return null;
        $model->update(['membresia' => 1]);
        $model->touch();
        $model->refresh();
        return $this->mapToPage($model);
    }

    public function unpublishPage(int $pageId): ?Page
    {
        $model = \App\Models\Template::find($pageId);
        if (!$model) return null;
        $model->update(['membresia' => 0]);
        $model->touch();
        $model->refresh();
        return $this->mapToPage($model);
    }
}
