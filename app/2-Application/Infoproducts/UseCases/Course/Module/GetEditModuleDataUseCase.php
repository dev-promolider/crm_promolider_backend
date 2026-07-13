<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Module;

use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;

class GetEditModuleDataUseCase extends Controller
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
            throw new InfoproductNotFoundException();
        }

        // Verificar si el usuario tiene permiso para acceder a los datos del curso
        if ($course->getUserId() !== $userId) {
            throw new InfoproductNotOwnedException();
        }

        $modules = $this->courseRepository->findModulesByCourseId($courseId);

        $data = [
            'course' => $course,
            'modules' => $modules,
        ];

        return $data;
    }
}
