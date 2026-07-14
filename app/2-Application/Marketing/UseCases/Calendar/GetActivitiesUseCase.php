<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class GetActivitiesUseCase
{
    public function __construct(
        private CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(int $userId): array
    {
        return $this->calendarRepository->getActivities($userId);
    }
}
