<?php

namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class GetClassTimeUseCase
{
    public function __construct(
        private PurchasedCourseRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $courseId, int $classId): array
    {
        $classTime = $this->repository->getClassTime($userId, $courseId, $classId);

        if ($classTime === null) {
            throw new \RuntimeException('Curso no comprado o no encontrado', 404);
        }

        return $classTime;
    }
}
