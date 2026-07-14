<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;
use Promolider\Domain\Marketing\Entities\CalendarNote;

class UpdateNoteUseCase
{
    public function __construct(
        private readonly CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(int $userId, int $noteId, array $data): ?CalendarNote
    {
        return $this->calendarRepository->updateNote($userId, $noteId, $data);
    }
}
