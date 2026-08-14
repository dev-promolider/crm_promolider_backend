<?php

namespace Promolider\Application\Preferences\UseCases;

use Promolider\Domain\Preferences\Contracts\PreferencesRepositoryInterface;

class GetPreferencesUseCase
{
    private PreferencesRepositoryInterface $preferencesRepository;

    public function __construct(PreferencesRepositoryInterface $preferencesRepository)
    {
        $this->preferencesRepository = $preferencesRepository;
    }

    public function execute(int $userId): array
    {
        return $this->preferencesRepository->getUserPreferences($userId);
    }
}
