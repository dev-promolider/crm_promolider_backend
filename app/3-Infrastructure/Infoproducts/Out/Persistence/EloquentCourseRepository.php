<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;
use Promolider\Domain\Infoproducts\Entities\Course\CourseConfiguration as CourseConfigurationEntity;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use Promolider\Domain\Infoproducts\Entities\Course\CourseObservation as CourseObservationEntity;
use App\Models\Course as EloquentCourse;
use App\Models\CourseObservation as EloquentCourseObservation;
use App\Models\Infoproduct\Course\Module as EloquentModule;
use App\Models\CourseConfiguration as EloquentCourseConfiguration;
use App\Models\Category as EloquentCategory;
use App\Models\CourseLevel as EloquentCourseLevel;
use App\Models\User as EloquentUser;
use App\Models\UserConfiguration as EloquentUserConfiguration;
use Illuminate\Support\Facades\DB;

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

    public function findOwnerId(int $courseId): ?int
    {
        $userId = EloquentCourse::query()
            ->whereKey($courseId)
            ->value('user_id');

        return $userId !== null
            ? (int) $userId
            : null;
    }

    public function findActiveObservations(int $courseId): array
    {
        return EloquentCourseObservation::query()
            ->where('id_course', $courseId)
            ->where('status', '1')
            ->orderByDesc('created_at')
            ->get()
            ->map(
                fn (EloquentCourseObservation $observation) =>
                    $this->toCourseObservationEntity($observation)
            )
            ->all();
    }

    public function findAdminIds(): array
    {
        return EloquentUser::query()
            ->where('id_account_type', 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function findCourseForReview(
        int $courseId
    ): ?array {
        $course = EloquentCourse::query()->find($courseId);

        if ($course === null) {
            return null;
        }

        $category = EloquentCategory::query()
            ->whereKey($course->id_categories)
            ->value('name');

        $level = EloquentCourseLevel::query()
            ->whereKey($course->course_level_id)
            ->value('name');

        $user = EloquentUser::query()
            ->find($course->user_id);

        return [
            'id' => (int) $course->id,
            'user_id' => (int) $course->user_id,
            'product_type_id' => (int) $course->product_type_id,
            'title' => (string) $course->title,
            'description' => $course->description,
            'price' => (float) $course->price,
            'currency' => $course->currency ?? 'soles',
            'status' => (int) $course->status,
            'certificate' => (int) $course->certificate,
            'category' => $category ?? 'Sin categoría',
            'level' => $level ?? 'Sin nivel',
            'months' => $course->months,
            'course_time' => $course->course_time ?? 0,
            'course_about' => $course->course_about,
            'will_learn' => $course->will_learn,
            'prev_knowledge' => $course->prev_knowledge,
            'course_for' => $course->course_for,
            'cover_image_url' => $course->url_portada,
            'instructor_name' => $user
                ? ($user->name ?? $user->username)
                : 'Sin nombre',
            'instructor_email' => $user
                ? $user->email
                : null,
            'instructor_phone' => $user
                ? ($user->phone ?? 'No especificado')
                : 'No especificado',
        ];
    }

    public function hasBookFiles(int $courseId): bool
    {
        return EloquentCourse::query()
            ->whereKey($courseId)
            ->whereHas('files')
            ->exists();
    }

    public function hasModules(int $courseId): bool
    {
        return EloquentModule::query()
            ->where('id_courses', $courseId)
            ->exists();
    }

    public function hasCourseConfiguration(
        int $courseId
    ): bool {
        return EloquentCourseConfiguration::query()
            ->where('course_id', $courseId)
            ->exists();
    }

    public function hasUserConfiguration(
        int $userId,
        int $configurationId
    ): bool {
        /*
         * Ajusta los nombres de modelo y columnas según la
         * consulta que actualmente utilizas en
         * getUserConfigurationCount().
         */
        return EloquentUserConfiguration::query()
            ->where('user_id', $userId)
            ->where('configuration_id', $configurationId)
            ->exists();
    }

    public function updateStatus(
        int $courseId,
        int $status
    ): void {
        EloquentCourse::query()
            ->whereKey($courseId)
            ->update([
                'status' => $status,
            ]);
    }

    public function storeCourseConfiguration(array $data): ?CourseConfigurationEntity
    {
        $courseConfiguration = EloquentCourseConfiguration::create($data);

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
        $courseConfiguration = EloquentCourseConfiguration::where('course_id', $courseId)->first();

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

    private function toCourseObservationEntity(
        EloquentCourseObservation $observation
    ): CourseObservationEntity {
        return new CourseObservationEntity(
            id: (int) $observation->id,
            analystId: (int) $observation->id_analyst,
            producerId: (int) $observation->id_productor,
            classId: (int) $observation->id_class,
            courseId: (int) $observation->id_course,
            description: (string) $observation->description,
            status: (string) $observation->status,
            createdAt: $observation->created_at
                ? $observation->created_at->toDateTimeString()
                : null,
            updatedAt: $observation->updated_at
                ? $observation->updated_at->toDateTimeString()
                : null
        );
    }

    public function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}
