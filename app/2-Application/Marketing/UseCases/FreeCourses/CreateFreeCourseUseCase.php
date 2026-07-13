<?php

namespace Promolider\Application\Marketing\UseCases\FreeCourses;

use Promolider\Domain\Marketing\Ports\Out\FreeCourseRepositoryInterface;

class CreateFreeCourseUseCase
{
    public function __construct(
        private readonly FreeCourseRepositoryInterface $repository,
    ) {}

    public function execute(array $data): array
    {
        $course = $this->repository->create($data);
        return $course->toArray();
    }
}
