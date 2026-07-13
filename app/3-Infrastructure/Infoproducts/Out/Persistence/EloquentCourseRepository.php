<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Entities\Course\Module;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use App\Models\Infoproduct\Course\Module as EloquentModule;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function findModulesByCourseId(int $courseId): array
    {
        $modules = EloquentModule::where('id_courses', $courseId)->get();

        return $modules->map(function ($module) {
            return new Module(
                $module->id,
                $module->id_courses,
                $module->name,
                $module->description,
                $module->order,
                $module->status
            );
        })->toArray();
    }
}
