<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;

class GetCourseDataUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
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

        $data = [
            'data' => $course,
        ];

        return $data;
    }
}
