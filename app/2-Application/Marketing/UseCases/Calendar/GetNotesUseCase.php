<?php

namespace Promolider\Application\Marketing\UseCases\Calendar;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;

class GetNotesUseCase
{
    public function __construct(
        private readonly CalendarRepositoryInterface $calendarRepository
    ) {}

    public function execute(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $notes = $this->calendarRepository->getNotes($userId, $startDate, $endDate);

        // Agrupar notas por fecha (Y-m-d) como lo hace el monolito
        $formattedNotes = [];
        foreach ($notes as $note) {
            // Asegurar que note tiene un array con las claves esperadas
            $noteDate = $note['date'] ?? '';
            if (!$noteDate) continue;

            $formattedNotes[$noteDate][] = [
                'id' => $note['id'] ?? null,
                'time' => $note['time_string'] ?? $note['time'] ?? '',
                'text' => $note['text'] ?? $note['content'] ?? '',
                'created_at' => $note['created_at'] ?? null,
                'updated_at' => $note['updated_at'] ?? null,
            ];
        }

        return $formattedNotes;
    }
}
