<?php

namespace Promolider\Application\Marketing\UseCases\FreeCourses;

use Promolider\Domain\Marketing\Ports\Out\FreeCourseRepositoryInterface;

class ListFreeCoursesUseCase
{
    public function __construct(
        private readonly FreeCourseRepositoryInterface $repository,
    ) {}

    public function execute(array $filters = []): array
    {
        return $this->repository->list($filters);
    }
}
