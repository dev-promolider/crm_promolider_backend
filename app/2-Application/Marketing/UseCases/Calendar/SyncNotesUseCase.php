<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class SyncNotesUseCase
{
    public function __construct(
        private CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(int $userId, array $notes): array
    {
        return $this->calendarRepository->syncNotes($userId, $notes);
    }
}
