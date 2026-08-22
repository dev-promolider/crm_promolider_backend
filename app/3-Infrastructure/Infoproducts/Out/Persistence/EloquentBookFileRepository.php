<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use App\Models\Infoproduct\Book\BookFile;
use Illuminate\Support\Facades\Log;
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

    public function findPreviewByCourseId(int $courseId): ?array
    {
        $file = BookFile::where('course_id', $courseId)
            ->where('is_preview', true)
            ->first();

        if (!$file) {
            return null;
        }

        return array_merge($this->toArray($file), [
            'url' => $this->inlinePreviewUrl($file->file_path),
        ]);
    }

    public function setPreview(int $courseId, ?int $bookFileId): void
    {
        BookFile::where('course_id', $courseId)
            ->where('is_preview', true)
            ->update(['is_preview' => false]);

        if ($bookFileId !== null) {
            BookFile::where('course_id', $courseId)
                ->where('id', $bookFileId)
                ->update(['is_preview' => true]);
        }
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
            'is_preview' => (bool) $file->is_preview,
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

        // Los nombres de archivo suelen traer espacios y tildes; sin codificar,
        // la URL no resuelve.
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        $storageDomain = rtrim(config('app.storage_domain', env('STORAGE_DOMAIN', '')), '/');

        if ($storageDomain) {
            return $storageDomain . '/' . $encoded;
        }

        return asset($encoded);
    }

    /**
     * URL firmada que obliga a S3 a devolver el archivo como PDF incrustable.
     *
     * Los archivos subidos antes de corregir el ContentType están guardados
     * como application/octet-stream, y con ese tipo el navegador los descarga
     * en lugar de mostrarlos: el visor de la muestra saldría en blanco. Los
     * parámetros response-* solo los respeta S3 en peticiones firmadas.
     */
    private function inlinePreviewUrl(?string $path): ?string
    {
        if (!$path || str_starts_with($path, 'http')) {
            return $this->normalizeMediaUrl($path);
        }

        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => ltrim($path, '/'),
                'ResponseContentType' => 'application/pdf',
                'ResponseContentDisposition' => 'inline',
            ]);

            return (string) $s3Client->createPresignedRequest($command, '+60 minutes')->getUri();

        } catch (\Throwable $th) {
            Log::warning('No se pudo firmar la URL de la muestra del libro', [
                'path' => $path,
                'error' => $th->getMessage(),
            ]);

            return $this->normalizeMediaUrl($path);
        }
    }
}
