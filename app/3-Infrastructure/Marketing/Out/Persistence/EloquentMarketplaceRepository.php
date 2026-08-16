<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use Promolider\Domain\Marketing\Ports\Out\MarketplaceRepositoryInterface;

class EloquentMarketplaceRepository implements MarketplaceRepositoryInterface
{
    public function getMarketplaceItems(string $type, array $filters = []): array
    {
        return match ($type) {
            'masterclass' => $this->getMasterclasses($filters),
            'ebook' => $this->getEbooks($filters),
            'mini-course', 'minicourse' => $this->getMiniCourses($filters),
            default => [],
        };
    }

    public function getCourses(array $filters = []): array
    {
        $query = \App\Models\Infoproduct\Infoproduct::query()
            ->select('courses.*', 'users.name as creator_name', 'users.last_name as creator_last_name')
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->where('courses.status', 2)
            ->where('courses.price', '>', 0);

        if (!empty($filters['search'])) {
            $query->where('courses.title', 'like', '%' . $filters['search'] . '%');
        }

        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 50;

        $results = $query->orderBy('courses.id', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $results->getCollection()->transform(function ($item) {
            $data = $item->toArray();
            $data['creator'] = trim(($item->creator_name ?? '') . ' ' . ($item->creator_last_name ?? ''));
            $data['category_name'] = $item->category->name ?? null;
            return $data;
        });

        return $results->toArray();
    }

    public function getCourseResources(int $courseId): array
    {
        $course = \App\Models\Infoproduct\Infoproduct::find($courseId);

        $masterclasses = \App\Models\Masterclass::with(['images', 'category'])
            ->where('course_id', $courseId)
            ->where('status', '!=', 0)
            ->get()
            ->map(function ($item) {
                $data = $item->toArray();
                $data['category_name'] = $item->category->name ?? null;
                $firstImage = $item->images->first();
                $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
                return $data;
            })->toArray();

        $ebooks = \App\Models\Ebook::with(['images', 'category'])
            ->where('course_id', $courseId)
            ->where('status', '!=', 0)
            ->get()
            ->map(function ($item) {
                $data = $item->toArray();
                $data['category_name'] = $item->category->name ?? null;
                $firstImage = $item->images->first();
                $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
                return $data;
            })->toArray();

        $minicourses = \App\Models\Minicourse::with(['images', 'category'])
            ->where('course_id', $courseId)
            ->where('status', '!=', 0)
            ->get()
            ->map(function ($item) {
                $data = $item->toArray();
                $data['category_name'] = $item->category->name ?? null;
                $firstImage = $item->images->first();
                $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
                return $data;
            })->toArray();

        return [
            'course' => $course ? $course->toArray() : null,
            'masterclasses' => $masterclasses,
            'ebooks' => $ebooks,
            'minicourses' => $minicourses,
            'promotional_materials' => []
        ];
    }


    public function getMasterclasses(array $filters = []): array
    {
        $query = \App\Models\Masterclass::with(['images', 'category'])
            ->where('status', '!=', 0);

        if (!empty($filters['category_id'])) {
            $query->where('id_categories', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 12;

        $results = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform to add category_name
        $results->getCollection()->transform(function ($item) {
            $data = $item->toArray();
            $data['category_name'] = $item->category->name ?? null;
            $firstImage = $item->images->first();
            $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
            return $data;
        });

        return $results->toArray();
    }

    public function getEbooks(array $filters = []): array
    {
        $query = \App\Models\Ebook::with(['images', 'category'])
            ->where('status', '!=', 0);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 12;

        $results = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $results->getCollection()->transform(function ($item) {
            $data = $item->toArray();
            $data['category_name'] = $item->category->name ?? null;
            $firstImage = $item->images->first();
            $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
            return $data;
        });

        return $results->toArray();
    }

    public function getMiniCourses(array $filters = []): array
    {
        $query = \App\Models\Minicourse::with(['images', 'category'])
            ->where('status', '!=', 0);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 12;

        $results = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $results->getCollection()->transform(function ($item) {
            $data = $item->toArray();
            $data['category_name'] = $item->category->name ?? null;
            $firstImage = $item->images->first();
            $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
            return $data;
        });

        return $results->toArray();
    }

    public function getCampaigns(): array
    {
        // El monolito combina masterclasses, ebooks y mini-courses con status=2
        $masterclasses = \App\Models\Masterclass::with(['images', 'category'])
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => array_merge($this->transformWithRelations($m), ['content_type' => 'masterclass']))
            ->toArray();

        $ebooks = \App\Models\Ebook::with(['images', 'category'])
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($e) => array_merge($this->transformWithRelations($e), ['content_type' => 'ebook']))
            ->toArray();

        $miniCourses = \App\Models\Minicourse::with(['images', 'category'])
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => array_merge($this->transformWithRelations($m), ['content_type' => 'minicourse']))
            ->toArray();

        return array_merge($masterclasses, $ebooks, $miniCourses);
    }

    public function toggleMarketplaceVisibility(int $courseId): bool
    {
        $course = \App\Models\Course::find($courseId);
        if (!$course) return false;

        if ($course->marketplace_listed) {
            $course->marketplace_listed = 0;
            $course->status = 0; // Se pasa a inactivo
        } else {
            $course->marketplace_listed = 1;
            $course->status = 2; // Se pasa a activo
        }
        return $course->save();
    }

    public function getCourseSubscribers(int $courseId): int
    {
        return \App\Models\PurchasedCourse::where('course_id', $courseId)->count();
    }

    public function getMasterclassDetail(int $id): ?array
    {
        $item = \App\Models\Masterclass::with(['images', 'category', 'documents', 'user'])->find($id);
        if (!$item) return null;
        $data = $item->toArray();
        $data['category_name'] = $item->category->name ?? null;

        // Convertir rutas relativas de imágenes a URLs completas
        if (!empty($data['images'])) {
            foreach ($data['images'] as &$img) {
                if (!empty($img['image'])) {
                    $img['image'] = $this->normalizeMediaUrl($img['image']);
                }
            }
        }

        // Convertir rutas relativas de documentos a URLs completas
        if (!empty($data['documents'])) {
            foreach ($data['documents'] as &$doc) {
                if (!empty($doc['document'])) {
                    $doc['document'] = $this->normalizeMediaUrl($doc['document']);
                }
            }
        }

        return $data;
    }

    public function getEbookDetail(int $id): ?array
    {
        $item = \App\Models\Ebook::with(['images', 'category', 'chapters', 'user'])->find($id);
        if (!$item) return null;
        $data = $item->toArray();
        $data['category_name'] = $item->category->name ?? null;

        // Convertir rutas relativas de imágenes a URLs completas
        if (!empty($data['images'])) {
            foreach ($data['images'] as &$img) {
                if (!empty($img['image'])) {
                    $img['image'] = $this->normalizeMediaUrl($img['image']);
                }
            }
        }

        // Convertir rutas relativas de capítulos/documentos a URLs completas
        if (!empty($data['chapters'])) {
            foreach ($data['chapters'] as &$chapter) {
                if (!empty($chapter['document'])) {
                    $chapter['document'] = $this->normalizeMediaUrl($chapter['document']);
                }
            }
        }

        return $data;
    }

    public function getMiniCourseDetail(int $id): ?array
    {
        $item = \App\Models\Minicourse::with([
            'images',
            'category',
            'modules.classes.documents',
            'classes.documents',
            'user'
        ])->find($id);
        if (!$item) return null;
        $data = $item->toArray();
        $data['category_name'] = $item->category->name ?? null;

        // Extraer videos desde las clases para compatibilidad con el viewer
        $videos = [];
        foreach ($item->classes as $class) {
            if ($class->video) {
                $videos[] = [
                    'id' => $class->id,
                    'title' => $class->title,
                    'description' => $class->description,
                    'video' => $class->video,
                    'duration' => $class->duration,
                    'order' => $class->order ?? count($videos) + 1,
                ];
            }
        }
        $data['videos'] = $videos;

        // Extraer documentos planos desde las clases
        $documents = [];
        foreach ($item->classes as $class) {
            foreach ($class->documents as $doc) {
                $documents[] = [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'document' => $doc->document ? $this->normalizeMediaUrl($doc->document) : null,
                ];
            }
        }
        $data['documents'] = $documents;

        // Convertir rutas relativas de imágenes a URLs completas
        if (!empty($data['images'])) {
            foreach ($data['images'] as &$img) {
                if (!empty($img['image'])) {
                    $img['image'] = $this->normalizeMediaUrl($img['image']);
                }
            }
        }
        unset($img);

        return $data;
    }

    private function transformWithRelations($item): array
    {
        $data = $item->toArray();
        $data['category_name'] = $item->category->name ?? null;
        $firstImage = $item->images->first();
        $data['image'] = $firstImage && $firstImage->image ? $this->normalizeMediaUrl($firstImage->image) : null;
        return $data;
    }

    /**
     * Normaliza rutas de medios almacenadas en BD a URLs públicas accesibles.
     *
     * La BD guarda rutas como "storage/masterclasses/X/Y/images/file.png"
     * (ya incluye el prefijo "storage/").
     *
     * En local:  STORAGE_DOMAIN=https://crm.promolider.info
     *            → https://crm.promolider.info/storage/masterclasses/X/Y/images/file.png
     *
     * En S3:     STORAGE_DOMAIN=https://bucket.s3-accelerate.amazonaws.com
     *            → Subir archivos manteniendo la misma estructura de carpetas.
     *
     * @param string|null $path Ruta almacenada en BD
     * @return string|null URL pública completa
     */
    private function normalizeMediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Si ya es una URL completa, devolver tal cual
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Normalizar dobles slashes (mantener protocolo intacto)
        $path = preg_replace('#(?<!:)//+#', '/', $path);
        $path = ltrim($path, '/');

        // Construir URL usando STORAGE_DOMAIN (preserva el prefijo storage/ del path)
        $storageDomain = rtrim(config('app.storage_domain', env('STORAGE_DOMAIN', '')), '/');

        if ($storageDomain) {
            return $storageDomain . '/' . $path;
        }

        // Fallback: asset() con el path tal cual viene de la BD
        return asset($path);
    }
}
