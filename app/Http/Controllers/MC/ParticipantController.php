<?php

namespace App\Http\Controllers\MC;

use App\Http\Controllers\Controller;
use App\Models\MasterClassParticipant;
use App\Models\MasterclassDistributor;
use App\Models\MasterClassVideo;
use App\Models\Masterclass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\CreateNotification;

class ParticipantController extends Controller
{
    public function subscribe(Request $request)
    {
        try {
            Log::info('Iniciando suscripción a MasterClass', $request->all());

            DB::beginTransaction();

            // Guardar participante
            $participant = new MasterClassParticipant();
            $participant->master_class_id = $request->masterClassId;
            $participant->fullname = $request->fullname;
            $participant->email = $request->email;
            $participant->phone = $request->phone;
            $participant->save();

            Log::info('Participante guardado con éxito', ['id' => $participant->id]);

            // Buscar el distribuidor
            $distributor = MasterclassDistributor::find($request->masterclass_distributor_id);

            if (!$distributor) {
                DB::rollBack();
                Log::error('No se encontró el masterclass_distributor', [
                    'id' => $request->masterclass_distributor_id
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Distribuidor no encontrado'
                ], 404);
            }

            $receiverUserId = $distributor->user_id;

            // Buscar el título de la masterclass
            $masterClass = Masterclass::find($request->masterClassId);

            $masterClassTitle = $masterClass ? $masterClass->title : 'Masterclass';

            $transmitterId = $request->masterclass_user_id;

            Log::info('Título de la masterclass obtenido', [
                'masterClassId' => $request->masterClassId,
                'title' => $masterClassTitle
            ]);

            // Crear notificación con título dinámico
            $request->merge([
                'transmitter' => $transmitterId,
                'receiver' => $receiverUserId,
                'title' => "Registro de Nuevo Estudiante",
                'body' => "{$request->fullname} se ha registrado al master class {$masterClassTitle}",
            ]);

            $saveNotification = CreateNotification::saveNotification($request);

            if ($saveNotification) {
                DB::commit();
                return response()->json(['message' => 'Te has registrado al master class correctamente'], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al crear la notificación'
                ], 500);
            }

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error en subscribe()', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function list()
    {
        try {
            $userId = auth()->id();
            Log::info('Listando participantes para usuario', ['user_id' => $userId]);

            $participants = MasterClassParticipant::with(['masterClass'])->get();

            Log::info('Participantes obtenidos', ['total' => $participants->count()]);

            return response()->json($participants, 200);

        } catch (\Throwable $th) {
            Log::error('Error en list()', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}