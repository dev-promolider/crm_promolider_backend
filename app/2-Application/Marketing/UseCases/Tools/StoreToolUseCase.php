<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class StoreToolUseCase
{
    public function __construct(
        private ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(string $type, array $data): int
    {
        return $this->toolRepository->storeTool($type, $data);
    }
}
