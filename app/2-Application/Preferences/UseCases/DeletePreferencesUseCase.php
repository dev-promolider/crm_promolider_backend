<?php

namespace Promolider\Application\Preferences\UseCases;

use Promolider\Domain\Preferences\Contracts\PreferencesRepositoryInterface;
use Exception;

class DeletePreferencesUseCase
{
    private PreferencesRepositoryInterface $preferencesRepository;

    public function __construct(PreferencesRepositoryInterface $preferencesRepository)
    {
        $this->preferencesRepository = $preferencesRepository;
    }

    public function execute(int $userId, array $categoryIds): void
    {
        if (empty($categoryIds)) {
            throw new Exception("Debe seleccionar al menos una categoría para eliminar.");
        }

        $this->preferencesRepository->deletePreferences($userId, $categoryIds);
    }
}
