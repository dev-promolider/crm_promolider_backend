<?php

namespace App\Services;

use App\Models\{Masterclass, CalendarNote, MeetingMasterclass};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Validation\ValidationException;

class CalendarService
{
    /**
     * Obtiene calendario de administrador
     */
    public function getAdminCalendar()
    {
        $masterclasses = Masterclass::select('id', 'title', 'date')->get();
        return response()->json(['data' => $masterclasses]);
    }

    /**
     * Obtiene calendario de productor
     */
    public function getProducerCalendar($id)
    {
        $masterclasses = Masterclass::where('user_id', $id)
            ->select('id', 'title', 'date')
            ->get();
        
        return response()->json(['data' => $masterclasses]);
    }

    /**
     * Obtiene calendario de distribuidor
     */
    public function getDistributorCalendar($id)
    {
        $masterclasses = Masterclass::join('masterclass_distributor', 'masterclasses.id', '=', 'masterclass_distributor.masterclass_id')
            ->where('masterclass_distributor.user_id', $id)
            ->select(
                'masterclasses.id as id',
                'masterclasses.title as title',
                'masterclasses.date as date'
            )
            ->get();
        
        return response()->json(['data' => $masterclasses]);
    }

    /**
     * Obtiene actividades de un usuario
     */
    public function getActivities($id)
    {
        $activities = MeetingMasterclass::where('owner_id', $id)
            ->select('id', 'date', 'time', 'comments', 'user_id')
            ->get();
        
        return response()->json(['data' => $activities]);
    }

    /**
     * Crea una reunión
     */
    public function createMeeting($request)
    {
        try {
            $validatedData = $request->validate([
                'date' => 'required|date',
                'time' => 'required',
                'masterclassId' => 'required',
                'participantId' => 'required',
                'title' => 'required',
                'owner_id' => 'required',
            ]);

            $meeting = MeetingMasterclass::create([
                'date' => $validatedData['date'],
                'time' => $validatedData['time'],
                'owner_id' => $validatedData['owner_id'],
                'comments' => $validatedData['title'],
                'user_id' => $validatedData['participantId'],
            ]);

            return response()->json([
                'message' => 'Reunión creada exitosamente',
                'meeting' => $meeting
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Obtiene notas del calendario
     */
    public function getNotes($userId, $startDate = null, $endDate = null)
    {
        try {
            $query = CalendarNote::forUser($userId)
                ->orderBy('date')
                ->orderBy('time');

            if ($startDate && $endDate) {
                $query->inDateRange($startDate, $endDate);
            }

            $notes = $query->get();

            $formattedNotes = [];
            foreach ($notes as $note) {
                $dateKey = $note->date->format('Y-m-d');

                if (!isset($formattedNotes[$dateKey])) {
                    $formattedNotes[$dateKey] = [];
                }

                $formattedNotes[$dateKey][] = [
                    'id' => $note->id,
                    'time' => $note->time_string,
                    'text' => $note->content,
                    'created_at' => $note->created_at->toISOString(),
                    'updated_at' => $note->updated_at->toISOString(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedNotes,
                'message' => 'Notas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener notas del calendario', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Sincroniza notas del calendario
     */
    public function syncNotes($userId, $notesToSync)
    {
        try {
            // Validar estructura básica
            if (!is_array($notesToSync)) {
                throw new \Exception('Formato de notas inválido');
            }

            DB::beginTransaction();

            foreach ($notesToSync as $dateKey => $dayNotes) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey)) {
                    continue;
                }

                foreach ($dayNotes as $noteData) {
                    if (!$this->validateNoteData($noteData)) {
                        continue;
                    }

                    if (isset($noteData['id']) && $noteData['id'] > 0) {
                        $this->updateExistingNote($userId, $noteData);
                    } else {
                        $this->createNewNote($userId, $dateKey, $noteData);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notas sincronizadas correctamente'
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Datos de notas inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al sincronizar notas', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar las notas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Crea una nueva nota
     */
    public function createNote($userId, $data)
    {
        try {
            $validated = validator($data, [
                'date' => 'required|date|date_format:Y-m-d',
                'time' => ['required', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
                'content' => 'required|string|max:1000',
            ])->validate();

            $note = CalendarNote::create([
                'user_id' => $userId,
                'date' => $validated['date'],
                'time' => $validated['time'],
                'content' => $validated['content']
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $note->id,
                    'time' => $note->time_string,
                    'text' => $note->content,
                    'date' => $note->date->format('Y-m-d')
                ],
                'message' => 'Nota creada correctamente'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al crear nota', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la nota',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Actualiza una nota existente
     */
    public function updateNote($userId, $noteId, $data)
    {
        try {
            $validated = validator($data, [
                'time' => ['sometimes', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
                'content' => 'sometimes|string|max:1000',
            ])->validate();

            $note = CalendarNote::where('id', $noteId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $note->update($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $note->id,
                    'time' => $note->time_string,
                    'text' => $note->content,
                    'date' => $note->date->format('Y-m-d')
                ],
                'message' => 'Nota actualizada correctamente'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al actualizar nota', [
                'user_id' => $userId,
                'note_id' => $noteId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la nota',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Elimina una nota
     */
    public function deleteNote($userId, $noteId)
    {
        try {
            $note = CalendarNote::where('id', $noteId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nota eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al eliminar nota', [
                'user_id' => $userId,
                'note_id' => $noteId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la nota',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    // ============= MÉTODOS PRIVADOS =============

    /**
     * Valida datos de nota
     */
    private function validateNoteData($noteData)
    {
        return isset($noteData['time']) 
            && isset($noteData['text'])
            && $this->validateTimeFormat($noteData['time'])
            && strlen($noteData['text']) <= 1000;
    }

    /**
     * Valida formato de tiempo
     */
    private function validateTimeFormat($time)
    {
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return false;
        }

        list($hour, $minute) = explode(':', $time);
        $hour = (int)$hour;
        $minute = (int)$minute;

        return ($hour >= 0 && $hour <= 23) && ($minute >= 0 && $minute <= 59);
    }

    /**
     * Actualiza nota existente
     */
    private function updateExistingNote($userId, $noteData)
    {
        $note = CalendarNote::where('id', $noteData['id'])
            ->where('user_id', $userId)
            ->first();

        if ($note) {
            $note->update([
                'time' => $noteData['time'],
                'content' => $noteData['text']
            ]);
        }
    }

    /**
     * Crea nueva nota
     */
    private function createNewNote($userId, $dateKey, $noteData)
    {
        CalendarNote::create([
            'user_id' => $userId,
            'date' => $dateKey,
            'time' => $noteData['time'],
            'content' => $noteData['text']
        ]);
    }
}