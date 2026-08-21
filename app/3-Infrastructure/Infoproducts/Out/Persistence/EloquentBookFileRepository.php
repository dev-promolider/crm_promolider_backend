<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use App\Models\Infoproduct\Book\BookFile;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;

class EloquentBookFileRepository implements BookFileRepositoryInterface
{
    public function findByCourseId(int $courseId): array
    {
        return BookFile::where('course_id', $courseId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (BookFile $file) => $this->toArray($file))
            ->all();
    }

    public function totalSizeByCourseId(int $courseId): int
    {
        return (int) BookFile::where('course_id', $courseId)->sum('size');
    }

    public function countByCourseId(int $courseId): int
    {
        return BookFile::where('course_id', $courseId)->count();
    }

    public function create(array $data): array
    {
        return $this->toArray(BookFile::create($data));
    }

    public function findById(int $bookFileId): ?array
    {
        $file = BookFile::find($bookFileId);

        return $file ? $this->toArray($file) : null;
    }

    public function delete(int $bookFileId): bool
    {
        $file = BookFile::find($bookFileId);

        if (!$file) {
            return false;
        }

        return (bool) $file->delete();
    }

    private function toArray(BookFile $file): array
    {
        return [
            'id' => $file->id,
            'course_id' => $file->course_id,
            'file_type' => $file->file_type,
            'file_name' => $file->file_name,
            'file_path' => $file->file_path,
            'mime_type' => $file->mime_type,
            'size' => (int) $file->size,
            'url' => $this->normalizeMediaUrl($file->file_path),
            'created_at' => $file->created_at,
        ];
    }

    /**
     * Misma convención que el resto de medios: si la ruta ya es absoluta se
     * devuelve tal cual, y si es relativa se antepone STORAGE_DOMAIN.
     */
    private function normalizeMediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = preg_replace('#(?<!:)//+#', '/', $path);
        $path = ltrim($path, '/');

        $storageDomain = rtrim(config('app.storage_domain', env('STORAGE_DOMAIN', '')), '/');

        if ($storageDomain) {
            return $storageDomain . '/' . $path;
        }

        return asset($path);
    }
}
