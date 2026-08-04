<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Storage;

use App\Models\ClassResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class ClassResourceService
{
    /**
     * @param array<int, UploadedFile> $resources
     */
    public function storeMany(
        array $resources,
        int $userId,
        int $courseId,
        int $classId
    ): void {
        foreach ($resources as $resource) {
            $this->store(
                resource: $resource,
                userId: $userId,
                courseId: $courseId,
                classId: $classId
            );
        }
    }

    private function store(
        UploadedFile $resource,
        int $userId,
        int $courseId,
        int $classId
    ): void {
        $filename = $this->formatFilename(
            $resource->getClientOriginalName()
        );

        $directory = sprintf(
            'courses/%d/%d/%d/resources',
            $userId,
            $courseId,
            $classId
        );

        $path = Storage::disk('s3')->putFileAs(
            $directory,
            $resource,
            $filename,
            'public'
        );

        if ($path === false) {
            throw new RuntimeException(
                "No se pudo guardar el recurso {$filename}."
            );
        }

        try {
            ClassResource::query()->create([
                'class_id' => $classId,
                'resource_file' => $path,
                'filename' => $filename,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('s3')->delete($path);

            throw $exception;
        }
    }

    public function removeMany(array $resources, int $classId): void
    {
        foreach ($resources as $resource) {
            $this->remove(
                resourceId: $resource,
                classId: $classId
            );
        }
    }

    private function remove(int $resourceId, int $classId): void
    {
        $resource = ClassResource::query()
            ->where('id', $resourceId)
            ->where('class_id', $classId)
            ->first();

        if ($resource === null) {
            throw new RuntimeException(
                "El recurso con ID {$resourceId} no existe."
            );
        }

        Storage::disk('s3')->delete($resource->resource_file);

        $resource->delete();
    }

    private function formatFilename(string $filename): string
    {
        return preg_replace(
            '/[^A-Za-z0-9_.-]/',
            '_',
            $filename
        );
    }
}
