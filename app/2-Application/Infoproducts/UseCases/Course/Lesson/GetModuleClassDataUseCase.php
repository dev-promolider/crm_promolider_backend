<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Lesson;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;

class GetModuleClassDataUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleClassRepositoryInterface $moduleClassRepository
    ) {}

    public function execute(int $moduleId): array
    {
        $module = $this->moduleRepository->findById($moduleId);

        if (!$module) {
            throw new \Exception("Module not found for the given course.", 404);
        }

        $lessons = $this->moduleClassRepository->findClassesByModuleId($moduleId);

        $data = [
            'data' => $lessons,
            'message' => 'Data recuperada con éxito'
        ];

        return $data;
    }
}
