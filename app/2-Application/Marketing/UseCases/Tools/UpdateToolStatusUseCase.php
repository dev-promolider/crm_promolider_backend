<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class UpdateToolStatusUseCase
{
    public function __construct(
        private readonly ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(string $type, int $toolId, string $status): bool
    {
        return $this->toolRepository->updateToolStatus($type, $toolId, $status);
    }
}
