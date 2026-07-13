<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class UpdateToolUseCase
{
    public function __construct(
        private readonly ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(string $type, int $toolId, array $data): bool
    {
        return $this->toolRepository->updateTool($type, $toolId, $data);
    }
}
