<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\CalendarNote;
use Promolider\Domain\Marketing\Entities\Meeting;

interface CalendarRepositoryInterface
{
    /** @return array */
    public function getAdminCalendar(): array;

    /** @return array */
    public function getProducerCalendar(int $producerId): array;

    /** @return array */
    public function getDistributorCalendar(int $distributorId): array;

    /** @return array */
    public function getActivities(int $userId): array;

    public function createMeeting(array $data): Meeting;

    /** @return CalendarNote[] */
    public function getNotes(int $userId, ?string $startDate = null, ?string $endDate = null): array;

    public function syncNotes(int $userId, array $notes): array;

    public function createNote(int $userId, array $data): CalendarNote;

    public function updateNote(int $userId, int $noteId, array $data): ?CalendarNote;

    public function deleteNote(int $userId, int $noteId): bool;
}
