<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Module;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;

class GetModuleDataUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository,
        private CourseRepositoryInterface $courseRepository
    ) {}

    public function execute(int $userId, int $courseId): array
    {
        $course = $this->infoproductRepository->findCourseById($courseId);

        // Verificar si el curso existe
        if (!$course) {
            throw new \Exception("El curso no existe.", 404);
        }

        // Verificar si el usuario tiene permiso para acceder a los datos del curso
        if ($course->getUserId() !== $userId) {
            throw new \Exception("El usuario no tiene permiso para acceder a los datos del curso.", 403);
        }

        $modules = $this->courseRepository->findModulesById($courseId);

        $data = [
            'data' => $modules,
        ];

        return $data;
    }
}
