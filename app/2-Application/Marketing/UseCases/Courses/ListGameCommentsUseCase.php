<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\GameCommentRepositoryInterface;

class ListGameCommentsUseCase
{
    public function __construct(
        private GameCommentRepositoryInterface $repository,
    ) {}

    public function execute(int $courseGameId): array
    {
        return $this->repository->listByGame($courseGameId);
    }
}
