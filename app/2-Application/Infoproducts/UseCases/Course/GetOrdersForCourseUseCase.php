<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;

class GetOrdersForCourseUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $courseId): array
    {
        $course = $this->infoproductRepository->findCourseById($courseId);

        // Verificar si el curso existe
        if (!$course) {
            throw new \Exception("Curso no encontrado", 404);
        }

        $data = $this->courseRepository->getOrdersById($courseId);

        return $data;
    }
}
