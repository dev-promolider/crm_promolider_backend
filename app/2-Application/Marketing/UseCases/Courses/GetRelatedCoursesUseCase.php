<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class GetRelatedCoursesUseCase
{
    public function __construct(
        private CourseRepositoryInterface $repository,
    ) {}

    public function execute(int $courseId, int $limit = 5, ?int $excludeUserId = null): array
    {
        return $this->repository->getRelatedCourses($courseId, $limit, $excludeUserId);
    }
}
