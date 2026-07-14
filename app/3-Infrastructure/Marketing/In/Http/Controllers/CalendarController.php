<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Calendar\GetCalendarEventsUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\GetActivitiesUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\CreateMeetingUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\GetNotesUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\SyncNotesUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\CreateNoteUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\UpdateNoteUseCase;
use Promolider\Application\Marketing\UseCases\Calendar\DeleteNoteUseCase;

class CalendarController extends Controller
{
    public function __construct(
        private GetCalendarEventsUseCase $getCalendarEventsUseCase,
        private GetActivitiesUseCase $getActivitiesUseCase,
        private CreateMeetingUseCase $createMeetingUseCase,
        private GetNotesUseCase $getNotesUseCase,
        private SyncNotesUseCase $syncNotesUseCase,
        private CreateNoteUseCase $createNoteUseCase,
        private UpdateNoteUseCase $updateNoteUseCase,
        private DeleteNoteUseCase $deleteNoteUseCase,
    ) {}

    public function getEventsAdmin(): \Illuminate\Http\JsonResponse
    {
        try {
            $events = $this->getCalendarEventsUseCase->getAdmin();
            return response()->json(['success' => true, 'data' => $events]);
        } catch (\Exception $e) {
            Log::error('Error getting calendar events: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener eventos'], 500);
        }
    }

    public function getEventsProducer(int $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $events = $this->getCalendarEventsUseCase->getProducer($userId);
            return response()->json(['success' => true, 'data' => $events]);
        } catch (\Exception $e) {
            Log::error('Error getting producer calendar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener eventos'], 500);
        }
    }

    public function getEventsDistributor(int $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $events = $this->getCalendarEventsUseCase->getDistributor($userId);
            return response()->json(['success' => true, 'data' => $events]);
        } catch (\Exception $e) {
            Log::error('Error getting distributor calendar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener eventos'], 500);
        }
    }

    public function getActivities(int $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $activities = $this->getActivitiesUseCase->execute($userId);
            return response()->json(['success' => true, 'data' => $activities]);
        } catch (\Exception $e) {
            Log::error('Error getting activities: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener actividades'], 500);
        }
    }

    public function createMeeting(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date',
                'link' => 'nullable|url',
                'type' => 'nullable|string',
            ]);
            $meeting = $this->createMeetingUseCase->execute($data);
            return response()->json(['success' => true, 'data' => $meeting], 201);
        } catch (\Exception $e) {
            Log::error('Error creating meeting: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear reunión'], 500);
        }
    }

    public function getNotes(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $notes = $this->getNotesUseCase->execute($userId, $startDate, $endDate);
            return response()->json(['success' => true, 'data' => $notes]);
        } catch (\Exception $e) {
            Log::error('Error getting notes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener notas'], 500);
        }
    }

    public function syncNotes(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $notes = $request->input('notes', []);
            $result = $this->syncNotesUseCase->execute($userId, $notes);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error syncing notes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al sincronizar notas'], 500);
        }
    }

    public function createNote(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $data = $request->validate([
                'date' => 'required|date',
                'time' => 'nullable|string',
                'content' => 'nullable|string',
            ]);
            $note = $this->createNoteUseCase->execute($userId, $data);
            return response()->json(['success' => true, 'data' => [
                'id' => $note->id,
                'user_id' => $note->userId,
                'date' => $note->date,
                'time' => $note->time,
                'text' => $note->content,
                'content' => $note->content,
                'created_at' => $note->createdAt,
                'updated_at' => $note->updatedAt,
            ]], 201);
        } catch (\Exception $e) {
            Log::error('Error creating note: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear nota'], 500);
        }
    }

    public function updateNote(Request $request, int $noteId): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $data = $request->validate([
                'date' => 'nullable|date',
                'time' => 'nullable|string',
                'content' => 'nullable|string',
            ]);
            $note = $this->updateNoteUseCase->execute($userId, $noteId, $data);
            if (!$note) {
                return response()->json(['success' => false, 'message' => 'Nota no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => [
                'id' => $note->id,
                'user_id' => $note->userId,
                'date' => $note->date,
                'time' => $note->time,
                'text' => $note->content,
                'content' => $note->content,
                'created_at' => $note->createdAt,
                'updated_at' => $note->updatedAt,
            ]]);
        } catch (\Exception $e) {
            Log::error('Error updating note: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar nota'], 500);
        }
    }

    public function deleteNote(Request $request, int $noteId): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->deleteNoteUseCase->execute($userId, $noteId);
            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            Log::error('Error deleting note: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar nota'], 500);
        }
    }
}
