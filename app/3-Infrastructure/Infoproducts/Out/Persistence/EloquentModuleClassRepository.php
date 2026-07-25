<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Entities\Course\Clas as ClassEntity;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use App\Models\Clas as EloquentClass;
use App\Models\Video as EloquentVideo;
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
            ->with([
                'module.course',
            ])
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
            'course_id' => (int) $class->module->course->id,
            'user_id' => (int) $class->module->course->user_id,
        ];
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
}
