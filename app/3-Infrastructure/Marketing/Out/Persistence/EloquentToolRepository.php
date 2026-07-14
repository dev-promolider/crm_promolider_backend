<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;
use Promolider\Domain\Marketing\Entities\Campaign;
use Promolider\Domain\Marketing\Entities\Category;

class EloquentToolRepository implements ToolRepositoryInterface
{
    public function getToolsByUser(int $userId): array
    {
        // IMPORTANTE: NO filtrar por status.
        // En el monolitio original, getToolsByUser muestra TODAS las herramientas
        // del usuario sin importar su estado (0=No publicado, 1=Publicado, 2=Privado).
        $masterclasses = \App\Models\Masterclass::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $ebooks = \App\Models\Ebook::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $miniCourses = \App\Models\Minicourse::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return [
            'masterclasses' => $masterclasses,
            'ebooks' => $ebooks,
            'mini_courses' => $miniCourses,
        ];
    }

    public function getCampaigns(): array
    {
        $masterclasses = \App\Models\Masterclass::where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $ebooks = \App\Models\Ebook::where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $miniCourses = \App\Models\Minicourse::where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return array_merge(
            array_map(fn($m) => array_merge($m, ['content_type' => 'masterclass']), $masterclasses),
            array_map(fn($e) => array_merge($e, ['content_type' => 'ebook']), $ebooks),
            array_map(fn($m) => array_merge($m, ['content_type' => 'minicourse']), $miniCourses)
        );
    }

    public function getUserCampaigns(int $userId): array
    {
        $masterclasses = \App\Models\Masterclass::where('user_id', $userId)
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $ebooks = \App\Models\Ebook::where('user_id', $userId)
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $miniCourses = \App\Models\Minicourse::where('user_id', $userId)
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return [
            'masterclasses' => $masterclasses,
            'ebooks' => $ebooks,
            'mini_courses' => $miniCourses,
        ];
    }

    public function getCampaignsByType(string $type): array
    {
        $modelClass = match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => null,
        };

        if (!$modelClass) return [];

        return $modelClass::where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getCategories(?string $type = null): array
    {
        $query = \App\Models\Category::query();
        if ($type) {
            $query->where('type', $type);
        }
        return $query->orderBy('name')->get()->toArray();
    }

    public function createCategory(array $data): Category
    {
        $model = \App\Models\Category::create($data);
        return new Category(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            type: $model->type,
            icon: $model->icon,
            parentId: $model->parent_id,
            createdAt: $model->created_at,
        );
    }

    public function getToolsWithStatus(int $userId): array
    {
        return $this->getToolsByUser($userId);
    }

    public function verifyToolOwnership(string $type, int $toolId, int $userId): bool
    {
        $modelClass = match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => null,
        };

        if (!$modelClass) return false;

        return $modelClass::where('id', $toolId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function updateToolStatus(string $type, int $toolId, string $status): bool
    {
        $modelClass = match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => null,
        };

        if (!$modelClass) return false;

        return $modelClass::where('id', $toolId)->update(['status' => $status]);
    }

    public function deleteTool(string $type, int $toolId): bool
    {
        $modelClass = match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => null,
        };

        if (!$modelClass) return false;

        return $modelClass::where('id', $toolId)->delete();
    }

    public function storeTool(string $type, array $data): int
    {
        $modelClass = match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => null,
        };

        if (!$modelClass) throw new \InvalidArgumentException("Invalid tool type: {$type}");

        $model = $modelClass::create($data);
        return $model->id;
    }

    public function getToolById(string $type, int $toolId): ?array
    {
        $query = match ($type) {
            'masterclass' => \App\Models\Masterclass::with('images', 'documents'),
            'ebook' => \App\Models\Ebook::with('images', 'documents', 'chapters'),
            'mini-course', 'minicourse' => \App\Models\Minicourse::with('images', 'modules'),
            default => null,
        };

        if (!$query) return null;

        $model = $query->find($toolId);
        if (!$model) return null;

        $data = $model->toArray();
        $data['type'] = $type;
        return $data;
    }

    public function updateTool(string $type, int $toolId, array $data): bool
    {
        $modelClass = match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => null,
        };

        if (!$modelClass) return false;

        $model = $modelClass::find($toolId);
        if (!$model) return false;

        return $model->update($data);
    }
}
