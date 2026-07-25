<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;
use Promolider\Domain\Infoproducts\Entities\Course\CourseConfiguration as CourseConfigurationEntity;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use App\Models\Course as EloquentCourse;
use App\Models\Infoproduct\Course\Module as EloquentModule;
use App\Models\CourseConfiguration as CourseConfigurationEloquent;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function findModulesById(int $courseId): array
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

    public function getOrdersById(int $courseId): array
    {
        $modules = EloquentModule::query()
            ->where('id_courses', $courseId)
            ->select([
                'id',
                'name',
                'order',
            ])
            ->with([
                'classes' => function ($query) {
                    $query
                        ->select([
                            'id',
                            'name',
                            'id_modules',
                            'order',
                        ])
                        ->orderBy('order');
                },
                'classes.video',
            ])
            ->orderBy('order')
            ->get();

        return $modules
            ->flatMap(function ($module) {
                $classes = $module->classes;

                $module->setAttribute('type', 'module');
                $module->unsetRelation('classes');

                $classes->each(function ($class) {
                    $class->setAttribute('type', 'class');
                });

                return collect([$module])->concat($classes);
            })
            ->values();
    }

    public function updateCourseStatus(int $courseId, int $status): void
    {
        $course = EloquentCourse::findOrFail($courseId);
        $course->status = $status;
        $course->save();
    }

    public function storeCourseConfiguration(array $data): ?CourseConfigurationEntity
    {
        $courseConfiguration = CourseConfigurationEloquent::create($data);

        return new CourseConfigurationEntity(
            $courseConfiguration->id,
            $courseConfiguration->course_id,
            $courseConfiguration->data,
            $courseConfiguration->condition_to_certificate,
            $courseConfiguration->type_certificate,
            $courseConfiguration->validated_by,
            $courseConfiguration->customized_certificate
        );
    }

    public function getCourseConfigurationData(int $courseId): ?CourseConfigurationEntity
    {
        $courseConfiguration = CourseConfigurationEloquent::where('course_id', $courseId)->first();

        if (!$courseConfiguration) {
            return null;
        }

        return new CourseConfigurationEntity(
            $courseConfiguration->id,
            $courseConfiguration->course_id,
            $courseConfiguration->data,
            $courseConfiguration->condition_to_certificate,
            $courseConfiguration->type_certificate,
            $courseConfiguration->validated_by,
            $courseConfiguration->customized_certificate
        );
    }
}
