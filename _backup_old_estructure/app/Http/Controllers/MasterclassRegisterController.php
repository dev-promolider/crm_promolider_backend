<?php

namespace App\Http\Controllers;

use App\Models\Masterclass;
use App\Models\MasterclassDistributor;
use App\Models\MasterclassUser;
use App\Http\Controllers\MC\ParticipantController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PHPMailerService;

class MasterclassRegisterController extends Controller
{

    public function index(Request $request)
    {
        $invitationCode = $request->query('invitation_code');

        // Buscar la invitación en la base de datos
        $invitation = MasterclassDistributor::where('code', $invitationCode)->first();

        if (!$invitation) {
            abort(404, 'Enlace de invitación no válido o expirado');
        }

        // Obtener la masterclass relacionada
        $masterclass = Masterclass::with('images')
            ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->select('masterclasses.*', 'categories.name as category_name')
            ->find($invitation->masterclass_id);

        // Obtener el usuario
        $user = User::find($invitation->user_id);
        $id_invitation = $invitation->id;

        // Pasar los datos a la vista
        return view('content.masterclass.register', compact('masterclass', 'user', 'id_invitation'));
    }

    public function updateParticipantStatus(Request $request, $user_id)
    {
        Log::info("🎯 [updateParticipantStatus] Solicitud recibida", [
            'user_id' => $user_id,
            'request' => $request->all()
        ]);
    
        // Validar ID
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|exists:masterclass_user,id',
        ]);
    
        if ($validator->fails()) {
            Log::warning("⚠️ [updateParticipantStatus] Validación fallida", [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'ID de usuario no válido',
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Validar body (el campo participant debe ser booleano)
        $request->validate([
            'participant' => 'required|in:0,1,2,3'
        ]);
    
        try {
            // Buscar el usuario
            $user = MasterclassUser::find($user_id);
            
            if (!$user) {
                Log::warning("⚠️ [updateParticipantStatus] Usuario no encontrado", [
                    'user_id' => $user_id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
        
            // Convertir true/false en 1/0
            $user->isParticipant = (int) $request->input('participant');
            $user->save();
        
            Log::info("✅ [updateParticipantStatus] Estado de participante actualizado", [
                'user_id' => $user->id,
                'email' => $user->email,
                'isParticipant' => $user->isParticipant
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Estado de participante actualizado exitosamente',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name . ' ' . $user->lastname,
                    'email' => $user->email,
                    'isParticipant' => (bool) $user->isParticipant
                ]
            ]);
        
        } catch (\Exception $e) {
            Log::error("❌ [updateParticipantStatus] Error al actualizar estado de participante", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateObservation(Request $request, $user_id)
    {
        Log::info("📝 [updateObservation] Solicitud recibida", [
            'user_id' => $user_id,
            'request' => $request->all()
        ]);

        // Validar ID
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|exists:masterclass_user,id',
        ]);

        if ($validator->fails()) {
            Log::warning("⚠️ [updateObservation] Validación fallida", [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ID de usuario no válido',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar body (campo observation requerido, string y máx 1000 caracteres)
        $request->validate([
            'observation' => 'required|string|max:1000'
        ]);

        try {
            // Buscar el usuario
            $user = MasterclassUser::find($user_id);

            if (!$user) {
                Log::warning("⚠️ [updateObservation] Usuario no encontrado", [
                    'user_id' => $user_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
        
            // Actualizar la observación
            $user->observation = $request->input('observation');
            $user->save();
        
            Log::info("✅ [updateObservation] Observación actualizada", [
                'user_id' => $user->id,
                'email' => $user->email,
                'observation' => $user->observation
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Observación actualizada exitosamente',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name . ' ' . $user->lastname,
                    'email' => $user->email,
                    'observation' => $user->observation
                ]
            ]);
        
        } catch (\Exception $e) {
            Log::error("❌ [updateObservation] Error al actualizar observación", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitRegistration(Request $request)
    {
        Log::info("📝 [submitRegistration] Datos recibidos", [
            'request' => $request->all()
        ]);
    
        // Primero obtenemos el distribuidor para saber a qué masterclass pertenece
        $invitation = MasterclassDistributor::where('id', $request->id_invitation)->first();
    
        if (!$invitation) {
            Log::warning("⚠️ [submitRegistration] Invitación no válida", [
                'id_invitation' => $request->id_invitation
            ]);
            return redirect()->back()->withErrors(['id_invitation' => 'Código de invitación no válido.']);
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('masterclass_user', 'email')
                    ->where(function ($query) use ($invitation) {
                        // Obtener todos los IDs de distribuidores que pertenecen a la misma masterclass
                        $distributorIds = MasterclassDistributor::where('masterclass_id', $invitation->masterclass_id)
                            ->pluck('id')
                            ->toArray();
                        
                        // Verificar que el email no exista en ninguno de esos distribuidores
                        return $query->whereIn('masterclass_distributor_id', $distributorIds);
                    }),
            ],
            'phone' => 'required|string|max:20',
            'nationality' => 'required|string',
            'age' => 'required|numeric|min:1|max:120',
            'id_invitation' => 'required|string',
            'user_type' => 'required|in:Guest,Affiliate',
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado en esta masterclass.',
        ]);
    
        if ($validator->fails()) {
            Log::warning("⚠️ [submitRegistration] Validación fallida", [
                'errors' => $validator->errors()->toArray()
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        try {
            DB::beginTransaction();
        
            // Crear usuario en masterclass_user
            $user = MasterclassUser::create([
                'masterclass_distributor_id' => $invitation->id,
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'age' => $request->age,
                'nationality' => $request->nationality,
                'user_type' => $request->user_type,
            ]);
        
            Log::info("✅ [submitRegistration] Usuario registrado correctamente", [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'invitation_id' => $invitation->id,
                'masterclass_id' => $invitation->masterclass_id
            ]);
        
            // Crear request para ParticipantController
            $participantRequest = new Request([
                'masterClassId' => $invitation->masterclass_id,
                'masterclass_distributor_id' => $invitation->id,
                'fullname' => $request->name . ' ' . $request->lastname,
                'masterclass_user_id' => $user->id,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
        
            $participantController = new ParticipantController();
            $subscribeResponse = $participantController->subscribe($participantRequest);
        
            if ($subscribeResponse->getStatusCode() !== 200) {
                DB::rollBack();
                $responseData = json_decode($subscribeResponse->getContent(), true);
                return back()->with('error', 'Error al suscribir al masterclass: ' . ($responseData['message'] ?? 'Error desconocido'));
            }

            // 📧 ENVIAR EMAIL DE CONFIRMACIÓN AL REGISTRADO
            try {
                Log::info('📧 Iniciando envío de email de confirmación de registro');
                
                // Obtener datos completos del masterclass
                $masterclass = Masterclass::with('category')->find($invitation->masterclass_id);
                
                if ($masterclass) {
                    $emailService = new PHPMailerService();
                    $emailService->sendEmailWithTemplate(
                        $request->email,
                        '✅ Confirmación de Inscripción - ' . $masterclass->title,
                        'emails.masterclass_registro',
                        [
                            // Datos del participante
                            'nombre' => $request->name,
                            'apellido' => $request->lastname,
                            'email' => $request->email,
                            'pais' => $request->nationality,
                            
                            // Datos de la masterclass
                            'masterclass_titulo' => $masterclass->title,
                            'masterclass_categoria' => $masterclass->category ? $masterclass->category->name : 'General',
                            'fecha' => date('d/m/Y', strtotime($masterclass->date)),
                            'hora' => $masterclass->hour,
                            'objetivos' => $masterclass->objectives ?? $masterclass->description,
                            
                            // Link del calendario (puedes generar uno real o dejarlo vacío)
                            'link_calendario' => $masterclass->meeting_link ?? '#',
                            
                            // Datos adicionales por compatibilidad
                            'userName' => $request->name . ' ' . $request->lastname,
                            'masterclassTitle' => $masterclass->title,
                            'masterclassDescription' => $masterclass->description,
                            'masterclassObjectives' => $masterclass->objectives,
                            'masterclassDate' => date('d/m/Y', strtotime($masterclass->date)),
                            'masterclassHour' => $masterclass->hour,
                            'masterclassDuration' => $masterclass->duration . ' minutos',
                            'meetingLink' => $masterclass->meeting_link,
                            'contactEmail' => $masterclass->email_contact,
                            'contactPhone' => $masterclass->phone_contact,
                            'year' => date('Y')
                        ]
                    );
                    
                    Log::info('✅ Email de confirmación enviado exitosamente', [
                        'masterclass_id' => $masterclass->id,
                        'masterclass_user_id' => $user->id,
                        'email_to' => $request->email,
                        'meeting_link' => $masterclass->meeting_link
                    ]);
                } else {
                    Log::warning('⚠️ No se pudo obtener datos del masterclass para enviar email', [
                        'masterclass_id' => $invitation->masterclass_id
                    ]);
                }
                
            } catch (\Exception $e) {
                // No fallar si el email falla - solo registrar el error
                Log::error('❌ Error al enviar email de confirmación de registro', [
                    'masterclass_user_id' => $user->id,
                    'email' => $request->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        
            DB::commit();
        
            return redirect()->back()->with('success', 'You have successfully registered for the masterclass!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ [submitRegistration] Error al registrar usuario", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'There was a problem registering your data: ' . $e->getMessage());
        }
    }

}
