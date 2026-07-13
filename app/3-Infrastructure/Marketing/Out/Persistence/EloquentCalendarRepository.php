<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;
use Promolider\Domain\Marketing\Entities\CalendarNote;
use Promolider\Domain\Marketing\Entities\Meeting;
use Illuminate\Support\Facades\Log;

class EloquentCalendarRepository implements CalendarRepositoryInterface
{
    public function getAdminCalendar(): array
    {
        return \App\Models\Masterclass::select('id', 'title', 'date')
            ->get()
            ->toArray();
    }

    public function getProducerCalendar(int $userId): array
    {
        return \App\Models\Masterclass::select('id', 'title', 'date')
            ->where('user_id', $userId)
            ->get()
            ->toArray();
    }

    public function getDistributorCalendar(int $distributorId): array
    {
        return \App\Models\Masterclass::select('masterclasses.id', 'masterclasses.title', 'masterclasses.date')
            ->join('masterclass_distributor', 'masterclasses.id', '=', 'masterclass_distributor.masterclass_id')
            ->where('masterclass_distributor.user_id', $distributorId)
            ->get()
            ->toArray();
    }

    public function getActivities(int $userId): array
    {
        return \App\Models\MeetingMasterclass::where('owner_id', $userId)
            ->orderBy('start_date')
            ->get()
            ->toArray();
    }

    public function createMeeting(array $data): Meeting
    {
        $model = \App\Models\MeetingMasterclass::create($data);
        return new Meeting(
            id: $model->id,
            userId: $model->owner_id,
            title: $model->title,
            description: $model->description,
            startDate: $model->start_date,
            endDate: $model->end_date,
            link: $model->link,
            type: $model->type,
            createdAt: $model->created_at,
        );
    }

    public function getNotes(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = \App\Models\CalendarNote::where('user_id', $userId);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->orderBy('date')->orderBy('time')->get()->toArray();
    }

    public function syncNotes(int $userId, array $notes): array
    {
        $result = [];
        foreach ($notes as $noteData) {
            if (!empty($noteData['date'])) {
                $existing = \App\Models\CalendarNote::where('user_id', $userId)
                    ->where('date', $noteData['date'])
                    ->where('time', $noteData['time'] ?? null)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'content' => $noteData['content'] ?? $existing->content,
                    ]);
                    $result[] = $existing->toArray();
                } else {
                    $created = \App\Models\CalendarNote::create([
                        'user_id' => $userId,
                        'date' => $noteData['date'],
                        'time' => $noteData['time'] ?? null,
                        'content' => $noteData['content'] ?? null,
                    ]);
                    $result[] = $created->toArray();
                }
            }
        }
        return $result;
    }

    public function createNote(int $userId, array $data): CalendarNote
    {
        $model = \App\Models\CalendarNote::create(array_merge($data, ['user_id' => $userId]));
        return new CalendarNote(
            id: $model->id,
            userId: $model->user_id,
            date: $model->date,
            time: $model->time,
            content: $model->content,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function updateNote(int $userId, int $noteId, array $data): ?CalendarNote
    {
        $note = \App\Models\CalendarNote::where('id', $noteId)->where('user_id', $userId)->first();
        if (!$note) return null;

        $note->update($data);
        $note->refresh();

        return new CalendarNote(
            id: $note->id,
            userId: $note->user_id,
            date: $note->date,
            time: $note->time,
            content: $note->content,
            createdAt: $note->created_at,
            updatedAt: $note->updated_at,
        );
    }

    public function deleteNote(int $userId, int $noteId): bool
    {
        return (bool) \App\Models\CalendarNote::where('id', $noteId)
            ->where('user_id', $userId)
            ->delete();
    }
}
