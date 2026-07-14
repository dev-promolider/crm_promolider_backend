<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class CreateNoteUseCase
{
    public function __construct(
        private CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(int $userId, array $data): \Promolider\Domain\Marketing\Entities\CalendarNote
    {
        return $this->calendarRepository->createNote($userId, $data);
    }
}
