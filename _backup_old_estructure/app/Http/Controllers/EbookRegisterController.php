<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Models\EbookDistributor;
use App\Helpers\CreateNotification;
use App\Models\EbookUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Services\PHPMailerService;
use Illuminate\Support\Str;

class EbookRegisterController extends Controller
{
    private $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        $this->phpMailerService = $phpMailerService;
    }

    public function ebookRegisterForm(Request $request)
    {
        $invitationCode = $request->query('invitation_code');

        $invitation = EbookDistributor::where('code', $invitationCode)->first();

        if (!$invitation) {
            abort(404, 'Enlace de invitación no válido o expirado');
        }

        $ebook = Ebook::with('images', 'category')->find($invitation->ebook_id);
        $user = User::find($invitation->user_id);
        $id_invitation = $invitation->id;

        return view('content.marketing.e-book.register', compact('ebook', 'user', 'id_invitation'));
    }

    public function updateParticipantStatus(Request $request, $user_id)
    {
        Log::info("🎯 [updateParticipantStatus] Solicitud recibida", [
            'user_id' => $user_id,
            'request' => $request->all()
        ]);
    
        // Validar ID
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|exists:ebook_users,id',
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
            $user = EbookUser::find($user_id);
            
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
            'user_id' => 'required|integer|exists:ebook_users,id',
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
            $user = EbookUser::find($user_id);
            
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

    public function ebookSubmitRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('ebook_users')->where(function ($query) use ($request) {
                    return $query->where('ebook_distributor_id', $request->id_invitation);
                }),
            ],
            'phone' => 'required|string|max:20',
            'nationality' => 'required|string',
            'age' => 'required|numeric|min:1|max:120',
            'id_invitation' => 'required|string',
        ]);

        $invitation = EbookDistributor::find($request->id_invitation);

        if (!$invitation) {
            return redirect()->back()->withErrors(['id_invitation' => 'Código de invitación no válido.']);
        }

        try {
            DB::beginTransaction();

            $ebookUser = EbookUser::create([
                'ebook_distributor_id' => $invitation->id,
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'age' => $request->age,
                'nationality' => $request->nationality,
            ]);

            // 🔔 Notificación al distribuidor
            CreateNotification::saveNotification(new Request([
                'receiver' => $invitation->user_id,
                'title'    => 'Registro de Nuevo Estudiante',
                'body'     => "{$ebookUser->name} {$ebookUser->lastname} se ha inscrito en tu e-book.",
            ]));

            // Enviar correo de confirmación usando Mailrelay API
            $this->sendEbookConfirmationEmail($ebookUser, $invitation->ebook_id);

            DB::commit();

            return redirect()->back()->with('success', '¡Te has inscrito correctamente al e-book! Revisa tu correo para más información.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar usuario en e-book', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'ebook_id' => $invitation->ebook_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    /**
     * Enviar correo de confirmación de registro al ebook usando Mailrelay API
     */
    private function sendEbookConfirmationEmail($ebookUser, $ebookId)
    {
        try {
            $ebook = Ebook::with('images', 'category', 'documents')->find($ebookId);

            Log::info('📧 Preparando envío de correo de confirmación de e-book', [
                'email' => $ebookUser->email,
                'ebook_id' => $ebookId,
                'ebook_title' => $ebook->title
            ]);

            // Obtener el enlace del documento PDF si existe
            $pdfLink = $ebook->documents->first() ? asset($ebook->documents->first()->document) : null;

            // Datos para la plantilla
            $emailData = [
                'user_name' => $ebookUser->name . ' ' . $ebookUser->lastname,
                'ebook_title' => $ebook->title,
                'ebook_description' => $ebook->description,
                'ebook' => $ebook,
                'nombre' => $ebookUser->name,
                'apellido' => $ebookUser->lastname,
                'email' => $ebookUser->email,
                'pdf_link' => $pdfLink
            ];

            // Preparar el asunto del correo
            $subject = '✅ Confirmación de Registro - E-book: ' . $ebook->title;

            // Enviar correo usando Mailrelay API
            $sent = $this->phpMailerService->sendEmailWithTemplate(
                $ebookUser->email,
                $subject,
                'emails.ebook-confirmation',
                $emailData,
                'Promolider'
            );

            if ($sent) {
                Log::info('✅ Correo de confirmación de e-book enviado exitosamente', [
                    'email' => $ebookUser->email,
                    'ebook_id' => $ebookId,
                    'ebook_title' => $ebook->title,
                    'timestamp' => now()->toDateTimeString()
                ]);
            } else {
                throw new \Exception('El correo no pudo ser enviado via Mailrelay API');
            }

        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de confirmación con Mailrelay API', [
                'error' => $e->getMessage(),
                'email' => $ebookUser->email,
                'ebook_id' => $ebookId,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            // No lanzar excepción para no bloquear el registro
            // El usuario ya está registrado, solo falló el email
        }
    }
}
