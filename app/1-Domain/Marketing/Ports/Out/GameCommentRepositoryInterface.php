<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\GameComment;

interface GameCommentRepositoryInterface
{
    /** @return array<int, array> */
    public function listByGame(int $courseGameId): array;

    public function create(array $data): array;
}
