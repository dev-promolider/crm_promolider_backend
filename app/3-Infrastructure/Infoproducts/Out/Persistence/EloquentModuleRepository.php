<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;
use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;
use App\Models\Module as EloquentModule;

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
