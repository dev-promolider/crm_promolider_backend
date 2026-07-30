<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;
use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;
use App\Models\Module as EloquentModule;
use App\Models\Course as EloquentCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EloquentModuleRepository implements ModuleRepositoryInterface
{
    public function findById(int $moduleId): ?ModuleEntity
    {
        $module = EloquentModule::query()->findOrFail($moduleId);

        return $this->toEntity($module);
    }

    public function findByCourseId(int $courseId): array
    {
        $modules = EloquentModule::where('id_courses', $courseId)->get();

        return $modules->map(function ($module) {
            return $this->toEntity($module);
        })->toArray();
    }

    public function findByIdAndCourseId(int $moduleId, int $courseId): ?ModuleEntity
    {
        $module = EloquentModule::where('id', $moduleId)
            ->where('id_courses', $courseId)
            ->first();

        if (!$module) {
            return null;
        }

        return $this->toEntity($module);
    }

    public function updateModuleStatus(int $moduleId, int $status): void
    {
        $module = EloquentModule::findOrFail($moduleId);
        $module->status = $status;
        $module->save();
    }

    public function createAtEnd(
        int $courseId,
        string $name
    ): ModuleEntity {
        return DB::transaction(function () use ($courseId, $name) {
            /*
             * Bloquea el curso mientras se calcula el siguiente orden.
             * Evita que dos peticiones creen módulos con el mismo orden.
             */
            EloquentCourse::query()
                ->whereKey($courseId)
                ->lockForUpdate()
                ->firstOrFail();

            $maxOrder = EloquentModule::query()
                ->where('id_courses', $courseId)
                ->max('order');

            $module = EloquentModule::query()->create([
                'id_courses' => $courseId,
                'name' => $name,
                'description' => null,
                'status' => 0,
                'order' => ((int) $maxOrder) + 1,
            ]);

            return $this->toEntity($module);
        });
    }

    public function belongsToUser(
        int $moduleId,
        int $userId
    ): bool {
        return EloquentModule::query()
            ->whereKey($moduleId)
            ->whereHas('course', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->exists();
    }

    public function updateName(
        int $moduleId,
        string $name
    ): ModuleEntity {
        $module = EloquentModule::query()
            ->findOrFail($moduleId);

        $module->update([
            'name' => $name,
        ]);

        return $this->toEntity($module->fresh());
    }

     public function deleteWithClasses(int $moduleId): void
    {
        $filesToDelete = [];

        DB::transaction(function () use (
            $moduleId,
            &$filesToDelete
        ) {
            $module = EloquentModule::query()
                ->with([
                    'classes.resources',
                    'classes.video',
                ])
                ->findOrFail($moduleId);

            foreach ($module->classes as $class) {
                foreach ($class->resources as $resource) {
                    if (!empty($resource->resource_file)) {
                        $filesToDelete[] = $resource->resource_file;
                    }

                    $resource->delete();
                }

                if ($class->video !== null) {
                    if (!empty($class->video->path)) {
                        $filesToDelete[] = $class->video->path;
                    }

                    $class->video->delete();
                }

                $class->delete();
            }

            $module->delete();
        });

        if (!empty($filesToDelete)) {
            Storage::disk('s3')->delete(
                array_unique($filesToDelete)
            );
        }
    }

    private function toEntity(EloquentModule $module): ModuleEntity
    {
        return new ModuleEntity(
            id: (int) $module->id,
            courseId: (int) $module->id_courses,
            name: $module->name,
            description: $module->description,
            order: (int) $module->order,
            status: (int) $module->status
        );
    }
}
