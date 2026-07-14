<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class DeleteToolUseCase
{
    public function __construct(
        private readonly ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(string $type, int $toolId): bool
    {
        return $this->toolRepository->deleteTool($type, $toolId);
    }
}
