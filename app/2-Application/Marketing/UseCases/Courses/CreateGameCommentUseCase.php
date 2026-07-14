<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\GameCommentRepositoryInterface;

class CreateGameCommentUseCase
{
    public function __construct(
        private GameCommentRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $courseGameId, string $content): array
    {
        return $this->repository->create([
            'id_author' => $userId,
            'id_course_games' => $courseGameId,
            'content' => $content,
        ]);
    }
}
