<?php

namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class GetPurchasedCourseUseCase
{
    public function __construct(
        private PurchasedCourseRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $courseId): array
    {
        $classesStatus = $this->repository->getClassesStatus($userId, $courseId);

        if ($classesStatus === null) {
            throw new \RuntimeException('No se encontró el curso para este usuario', 404);
        }

        return $classesStatus;
    }
}
