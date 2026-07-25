<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Class;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;
use Promolider\Infrastructure\Infoproducts\Out\Storage\ClassResourceService;

use Illuminate\Support\Str;

class SaveModuleClassUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository,
        private CourseRepositoryInterface $courseRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleClassRepositoryInterface $moduleClassRepository,
        private ClassResourceService $classResourceService
    ) {}

    public function execute(
        int $userId,
        int $moduleId,
        array $data,
        array $resources = []
    ): array {
        return $this->moduleClassRepository->transaction(
            function () use ($userId, $moduleId, $data, $resources) {
                $module = $this->moduleRepository
                        ->findById($moduleId); 

                $course = $this->infoproductRepository->findCourseById($module->getCourseId());

                if ($course->getUserId() !== $userId) {
                    throw new \Exception(
                        'No tienes acceso a este curso.'
                    );
                }

                $class = $this->moduleClassRepository->createClass([
                    'id_modules' => $module->getId(),
                    'name' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'description' => $data['description'] ?? null,
                    'time' => $data['time'] ?? null,
                    'url' => '/class/example',
                ]);

                if ((int) $course->getStatus() === 2) {
                    $this->courseRepository->updateCourseStatus(
                        courseId: $course->getId(),
                        status: 4
                    );
                }

                if (
                    (int) $module->getStatus() !== 4
                ) {
                    $this->moduleRepository->updateModuleStatus(
                        moduleId: (int) $module->getId(),
                        status: 4
                    );
                }

                if (!empty($resources)) {
                    $this->classResourceService->storeMany(
                        resources: $resources,
                        userId: $userId,
                        courseId: (int) $course->getId(),
                        classId: $class->getId()
                    );
                }

                $classes = $this->moduleClassRepository
                    ->findClassesByModuleId($moduleId);

                return [
                    'status' => 'ok',
                    'class' => $class,
                    'classes' => $classes,
                ];
            }
        );
    }
}
