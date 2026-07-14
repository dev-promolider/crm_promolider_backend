<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class GetToolsUseCase
{
    public function __construct(
        private ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(int $userId): array
    {
        $grouped = $this->toolRepository->getToolsByUser($userId);

        // Aplanar en un solo array con campo 'type' (el frontend espera un array plano)
        $tools = [];

        foreach ($grouped['masterclasses'] ?? [] as $item) {
            $item['type'] = 'masterclass';
            $tools[] = $item;
        }
        foreach ($grouped['ebooks'] ?? [] as $item) {
            $item['type'] = 'ebook';
            $tools[] = $item;
        }
        foreach ($grouped['mini_courses'] ?? [] as $item) {
            $item['type'] = 'minicourse';
            $tools[] = $item;
        }

        return $tools;
    }
}
