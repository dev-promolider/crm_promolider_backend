<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Entities\Course\Clas as ClassEntity;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use App\Models\Clas as EloquentClass;
use App\Models\Video as EloquentVideo;
use App\Models\Module as EloquentModule;
use App\Models\Course as EloquentCourse;
use App\Models\ClassResource as EloquentClassResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EloquentModuleClassRepository implements ModuleClassRepositoryInterface
{
    public function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    public function findClassesByModuleId(int $moduleId): array
    {
        $lessons = EloquentClass::where('id_modules', $moduleId)
                                ->orderBy('order', 'asc')
                                ->get()
                                ->map(function ($lesson) {
                                    $lesson->has_video = EloquentVideo::where('class_id', $lesson->id)->exists();
                                    return $lesson;
                                });

        return $lessons->map(function ($lesson) {
            return new ClassEntity(
                $lesson->id,
                $lesson->id_modules,
                $lesson->name,
                $lesson->slug,
                $lesson->time,
                $lesson->url,
                $lesson->description,
                $lesson->order,
                $lesson->status,
                $lesson->progress,
                $lesson->has_video
            );
        })->toArray();
    }

    public function createClass(array $data): ClassEntity
    {
        $class = EloquentClass::create($data);

        return new ClassEntity(
            $class->id,
            $class->id_modules,
            $class->name,
            $class->slug,
            $class->time,
            $class->url,
            $class->description,
            $class->order,
            $class->status,
            $class->progress,
            false
        );
    }

    public function findClassContext(int $classId): ?array
    {
        $class = EloquentClass::query()
            ->with('module.course')
            ->find($classId);

        if (
            $class === null ||
            $class->module === null ||
            $class->module->course === null
        ) {
            return null;
        }

        return [
            'class_id' => (int) $class->id,
            'module_id' => (int) $class->module->id,
            'course_id' => (int) $class->module->course->id,
            'user_id' => (int) $class->module->course->user_id,
        ];
    }

    public function findClassDetails(int $classId): ?array
    {
        $class = EloquentClass::query()
            ->with([
                'resources',
                'video',
            ])
            ->find($classId);

        if ($class === null) {
            return null;
        }

        $resources = $class->resources
            ->map(function ($resource) {
                return [
                    'id' => (int) $resource->id,
                    'class_id' => (int) $resource->class_id,
                    'resource_file' => $resource->resource_file,
                    'filename' => $resource->filename,
                    'created_at' => $resource->created_at
                        ? $resource->created_at->toDateTimeString()
                        : null,
                ];
            })
            ->all();

        $video = $class->video !== null
            ? [
                'id' => (int) $class->video->id,
                'class_id' => (int) $class->video->class_id,
                'filename' => $class->video->filename,
                'path' => $class->video->path,
                'saved_time' => (int) $class->video->saved_time,
                'created_at' => $class->video->created_at
                    ? $class->video->created_at->toDateTimeString()
                    : null,
            ]
            : null;

        return [
            'resources' => $resources,
            'video' => $video,
            'has_video' => $video !== null,
        ];
    }

    public function updateClass(
        int $classId,
        array $data
    ): void {
        EloquentClass::query()
            ->whereKey($classId)
            ->update($data);
    }

    public function updateModuleStatus(
        int $moduleId,
        int $status
    ): void {
        EloquentModule::query()
            ->whereKey($moduleId)
            ->update([
                'status' => $status,
            ]);
    }

    public function updateCourseStatus(
        int $courseId,
        int $status
    ): void {
        EloquentCourse::query()
            ->whereKey($courseId)
            ->update([
                'status' => $status,
            ]);
    }

    public function saveVideoInformation(
        int $classId,
        string $filename,
        string $path
    ): void {
        $class = EloquentClass::query()->findOrFail($classId);

        /*
         * Se conserva la búsqueda por class_id para reconocer
         * registros antiguos que tienen videoable_type = "test"
         * y videoable_id = 0.
         */
        $video = EloquentVideo::query()
            ->where('class_id', $classId)
            ->first();

        if ($video === null) {
            $video = new EloquentVideo();
        }

        $video->class_id = $classId;
        $video->filename = $filename;
        $video->path = $path;
        $video->saved_time = 0;

        /*
         * Esto asigna automáticamente:
         *
         * videoable_id   = ID de la clase
         * videoable_type = clase Eloquent correspondiente
         */
        $video->videoable()->associate($class);

        $video->save();
    }

    public function deleteClassWithRelations(int $classId): void
    {
        $filesToDelete = [];

        DB::transaction(function () use (
            $classId,
            &$filesToDelete
        ) {
            $class = EloquentClass::query()
                ->lockForUpdate()
                ->findOrFail($classId);

            $resources = EloquentClassResource::query()
                ->where('class_id', $classId)
                ->get();

            $videos = EloquentVideo::query()
                ->where('class_id', $classId)
                ->get();

            foreach ($resources as $resource) {
                if (!empty($resource->resource_file)) {
                    $filesToDelete[] = $resource->resource_file;
                }
            }

            foreach ($videos as $video) {
                if (!empty($video->path)) {
                    $filesToDelete[] = $video->path;
                }
            }

            EloquentClassResource::query()
                ->where('class_id', $classId)
                ->delete();

            EloquentVideo::query()
                ->where('class_id', $classId)
                ->delete();

            $class->delete();
        });

        $filesToDelete = array_values(
            array_unique(
                array_filter($filesToDelete)
            )
        );

        if (empty($filesToDelete)) {
            return;
        }

        $deleted = Storage::disk('s3')->delete($filesToDelete);

        if (!$deleted) {
            Log::warning(
                'La clase fue eliminada, pero algunos archivos no pudieron eliminarse de S3.',
                [
                    'class_id' => $classId,
                    'files' => $filesToDelete,
                ]
            );
        }
    }
}
