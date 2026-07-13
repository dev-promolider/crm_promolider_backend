<?php

namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class UpdateClassStatusUseCase
{
    public function __construct(
        private PurchasedCourseRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $courseId, int $classId): array
    {
        $this->repository->updateClassStatus($userId, $courseId, $classId, 'SEEN');

        $purchased = $this->repository->findByUserAndCourse($userId, $courseId);

        if (!$purchased) {
            throw new \RuntimeException('Curso comprado no encontrado', 404);
        }

        return $purchased;
    }
}
