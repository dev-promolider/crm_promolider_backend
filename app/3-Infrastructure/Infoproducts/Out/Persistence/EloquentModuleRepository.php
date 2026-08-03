<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;
use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;
use App\Models\Module as EloquentModule;

class EloquentModuleRepository implements ModuleRepositoryInterface
{
    public function findById(int $moduleId): ?ModuleEntity
    {
        $module = EloquentModule::find($moduleId);

        if (!$module) {
            return null;
        }

        return new ModuleEntity(
            $module->id,
            $module->id_courses,
            $module->name,
            $module->description,
            $module->order,
            $module->status
        );
    }

    public function findByCourseId(int $courseId): array
    {
        $modules = EloquentModule::where('id_courses', $courseId)->get();

        return $modules->map(function ($module) {
            return new ModuleEntity(
                $module->id,
                $module->id_courses,
                $module->name,
                $module->description,
                $module->order,
                $module->status
            );
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

        return new ModuleEntity(
            $module->id,
            $module->id_courses,
            $module->name,
            $module->description,
            $module->order,
            $module->status
        );
    }
}
