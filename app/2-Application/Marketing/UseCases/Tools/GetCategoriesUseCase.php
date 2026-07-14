<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class GetCategoriesUseCase
{
    public function __construct(
        private ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(?string $type = null): array
    {
        return $this->toolRepository->getCategories($type);
    }

    public function create(array $data): \Promolider\Domain\Marketing\Entities\Category
    {
        return $this->toolRepository->createCategory($data);
    }
}
