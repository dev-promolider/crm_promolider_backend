<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class GetCalendarEventsUseCase
{
    public function __construct(
        private readonly CalendarRepositoryInterface $calendarRepository
    ) {}

    public function getAdmin(): array
    {
        return $this->calendarRepository->getAdminCalendar();
    }

    public function getProducer(int $producerId): array
    {
        return $this->calendarRepository->getProducerCalendar($producerId);
    }

    public function getDistributor(int $distributorId): array
    {
        return $this->calendarRepository->getDistributorCalendar($distributorId);
    }
}
