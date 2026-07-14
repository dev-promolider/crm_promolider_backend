<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class DeleteNoteUseCase
{
    public function __construct(
        private readonly CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(int $userId, int $noteId): bool
    {
        return $this->calendarRepository->deleteNote($userId, $noteId);
    }
}
