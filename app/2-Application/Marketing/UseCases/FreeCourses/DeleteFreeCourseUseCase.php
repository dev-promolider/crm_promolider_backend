<?php

namespace Promolider\Application\Marketing\UseCases\FreeCourses;

use Promolider\Domain\Marketing\Ports\Out\FreeCourseRepositoryInterface;

class DeleteFreeCourseUseCase
{
    public function __construct(
        private FreeCourseRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
