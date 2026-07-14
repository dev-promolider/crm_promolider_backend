<?php

namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class GetLastClassPlayedUseCase
{
    public function __construct(
        private PurchasedCourseRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $courseId): ?array
    {
        return $this->repository->getLastClassPlayed($userId, $courseId);
    }
}
