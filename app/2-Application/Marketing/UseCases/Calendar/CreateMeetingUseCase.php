<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class CreateMeetingUseCase
{
    public function __construct(
        private readonly CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(array $data): \Promolider\Domain\Marketing\Entities\Meeting
    {
        return $this->calendarRepository->createMeeting($data);
    }
}
