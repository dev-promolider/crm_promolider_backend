<?php

namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class SaveClassSeenUseCase
{
    public function __construct(
        private PurchasedCourseRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $courseId, int $classId, ?string $displayTime): array
    {
        $this->repository->saveClassSeen($userId, $courseId, $classId, $displayTime);

        $purchased = $this->repository->findByUserAndCourse($userId, $courseId);

        if (!$purchased) {
            throw new \RuntimeException('Curso comprado no encontrado', 404);
        }

        return $purchased;
    }
}
