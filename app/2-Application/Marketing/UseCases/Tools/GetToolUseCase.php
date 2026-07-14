<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class GetToolUseCase
{
    public function __construct(
        private ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(string $type, int $toolId): ?array
    {
        return $this->toolRepository->getToolById($type, $toolId);
    }
}
