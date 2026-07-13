<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class SearchCoursesUseCase
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository,
    ) {}

    public function execute(string $query, ?int $userId = null, array $filters = []): array
    {
        return $this->repository->searchCourses($query, $userId, $filters);
    }
}
