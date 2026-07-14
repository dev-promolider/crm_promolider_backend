<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class GetCourseExpirationUseCase
{
    public function __construct(
        private CourseRepositoryInterface $repository,
    ) {}

    public function execute(int $courseId, int $userId): ?array
    {
        return $this->repository->getCourseExpiration($courseId, $userId);
    }
}
