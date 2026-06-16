<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MiniCourse;
use App\Models\MiniCourseDistributor;
use App\Models\MiniCourseUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\CreateNotification;
use Illuminate\Support\Facades\Log;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MinicourseRegisterController extends Controller
{
    private $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        $this->phpMailerService = $phpMailerService;
    }

    public function miniCourseRegisterForm(Request $request)
    {
        $invitationCode = $request->query('invitation_code');

        $invitation = MiniCourseDistributor::where('code', $invitationCode)->first();

        if (!$invitation) {
            abort(404, 'Enlace de invitación no válido o expirado');
        }

        $mini_course = MiniCourse::with('images', 'category')->find($invitation->mini_course_id);
        $user = User::find($invitation->user_id);
        $id_invitation = $invitation->id;

        return view('content.marketing.mini-course.register', compact('mini_course', 'user', 'id_invitation'));
    }

    public function miniCourseSubmitRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'nationality' => 'required|string',
            'age' => 'required|numeric|min:1|max:120',
            'id_invitation' => 'required|integer|exists:mini_course_distributors,id',
        ]);

        $invitation = MiniCourseDistributor::find($request->id_invitation);

        if (!$invitation) {
            return redirect()->back()->withErrors(['id_invitation' => 'Código de invitación no válido.']);
        }

        try {
            DB::beginTransaction();

            // Crear el registro del usuario
            $miniCourseUser = MiniCourseUser::create([
                'mini_course_distributors_id' => $invitation->id,
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'age' => $request->age,
                'nationality' => $request->nationality,
            ]);

            // 🔔 Notificación al distribuidor
            CreateNotification::saveNotification(new Request([
                'receiver' => $invitation->user_id, // el dueño de la invitación
                'title'    => 'Registro de Nuevo Estudiante',
                'body'     => "{$miniCourseUser->name} {$miniCourseUser->lastname} se ha inscrito en tu e-book.",
            ]));

            // Generar enlace de acceso único para el usuario
            $accessLink = $this->generateUserAccessLink($miniCourseUser, $invitation->mini_course_id);

            // Enviar correo con el enlace de acceso usando PHPMailer
            $this->sendMiniCourseAccessEmail($miniCourseUser, $accessLink, $invitation->mini_course_id);

            DB::commit();

            return redirect()->back()->with('success', '¡Te has inscrito correctamente al mini curso! Revisa tu correo para acceder al contenido.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar usuario en mini curso', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'mini_course_id' => $invitation->mini_course_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    public function updateParticipantStatus(Request $request, $user_id)
    {
        Log::info("🎯 [updateParticipantStatus] Solicitud recibida", [
            'user_id' => $user_id,
            'request' => $request->all()
        ]);
    
        // Validar ID
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|exists:mini_course_users,id',
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
            $user = MiniCourseUser::find($user_id);
            
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
            'user_id' => 'required|integer|exists:mini_course_users,id',
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
            $user = MiniCourseUser::find($user_id);

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

    /**
     * Generar enlace de acceso único para el usuario registrado
     */
    private function generateUserAccessLink($miniCourseUser, $miniCourseId)
    {
        // Generar un token único para el acceso del usuario
        $accessToken = Str::random(32);
        
        // Actualizar el registro con el token de acceso
        $miniCourseUser->update([
            'access_token' => $accessToken,
            'token_expires_at' => now()->addDays(30) // Token válido por 30 días
        ]);

        // Generar la URL de acceso
        return url("/mini-course/access/{$miniCourseId}?token={$accessToken}");
    }

    /**
     * Enviar correo con enlace de acceso al mini curso usando Mailrelay API
     */
    private function sendMiniCourseAccessEmail($miniCourseUser, $accessLink, $miniCourseId)
    {
        try {
            $miniCourse = MiniCourse::with('images', 'category')->find($miniCourseId);

            Log::info('📧 Preparando envío de correo de acceso al mini curso', [
                'email' => $miniCourseUser->email,
                'mini_course_id' => $miniCourseId,
                'mini_course_title' => $miniCourse->title
            ]);

            // Datos para la plantilla
            $emailData = [
                'user_name' => $miniCourseUser->name . ' ' . $miniCourseUser->lastname,
                'course_title' => $miniCourse->title,
                'course_description' => $miniCourse->description,
                'access_link' => $accessLink,
                'mini_course' => $miniCourse,
                'nombre' => $miniCourseUser->name,
                'apellido' => $miniCourseUser->lastname,
                'email' => $miniCourseUser->email
            ];

            // Preparar el asunto del correo
            $subject = '✅ Acceso a tu Mini Curso: ' . $miniCourse->title;

            // Enviar correo usando Mailrelay API
            $sent = $this->phpMailerService->sendEmailWithTemplate(
                $miniCourseUser->email,
                $subject,
                'emails.minicourse-access',
                $emailData,
                'Promolider'
            );

            if ($sent) {
                Log::info('✅ Correo de acceso al mini curso enviado exitosamente', [
                    'email' => $miniCourseUser->email,
                    'mini_course_id' => $miniCourseId,
                    'mini_course_title' => $miniCourse->title,
                    'timestamp' => now()->toDateTimeString()
                ]);
            } else {
                throw new \Exception('El correo no pudo ser enviado via Mailrelay API');
            }

        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de acceso con Mailrelay API', [
                'error' => $e->getMessage(),
                'email' => $miniCourseUser->email,
                'mini_course_id' => $miniCourseId,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            // No lanzar excepción para no bloquear el registro
            // El usuario ya está registrado, solo falló el email
        }
    }



    /**
     * Acceso al mini curso mediante token
     */
    public function accessMiniCourse($id, Request $request)
    {
        $token = $request->query('token');
        Log::info('🔑 Iniciando acceso a mini curso', [
            'mini_course_id' => $id,
            'token' => $token
        ]);
    
        if (!$token) {
            Log::warning('❌ Token no enviado en acceso a mini curso', [
                'mini_course_id' => $id
            ]);
            abort(404, 'Token de acceso requerido');
        }
    
        // Buscar el usuario registrado con el token válido
        $miniCourseUser = MiniCourseUser::whereHas('distributor', function ($query) use ($id) {
            $query->where('mini_course_id', $id);
        })
        ->where('access_token', $token)
        ->where('token_expires_at', '>', now())
        ->first();
    
        if (!$miniCourseUser) {
            Log::warning('❌ Token inválido o expirado', [
                'mini_course_id' => $id,
                'token' => $token
            ]);
            abort(404, 'Token de acceso inválido o expirado');
        }
    
        Log::info('✅ Usuario validado con token', [
            'mini_course_id' => $id,
            'user_id' => $miniCourseUser->id,
            'user_email' => $miniCourseUser->email,
            'token_expira' => $miniCourseUser->token_expires_at
        ]);
    
        try {
            // Obtener el mini curso con toda la información
            $miniCourse = MiniCourse::with([
                'modules',
                'images',
                'classes.documents' // ✅ igual que en viewMiniCourse
            ])->where('id', $id)->first();
            
            if (!$miniCourse) {
                Log::error('❌ Mini curso no encontrado', [
                    'mini_course_id' => $id
                ]);
                abort(404, 'Mini curso no encontrado');
            }
        
            // Formatear rutas
            $miniCourse->images->each(fn($img) => $img->image = asset($img->image));
        
            $miniCourse->classes->each(function($class) {
                $class->video = asset($class->video);
                $class->documents->each(fn($doc) => $doc->document = asset($doc->document));
            });
        
            Log::info('🚀 Usuario accedió al mini curso', [
                'user_email' => $miniCourseUser->email,
                'mini_course_id' => $id,
                'access_time' => now()->toDateTimeString()
            ]);
        
            return view('content.marketing.mini-course.module', compact('miniCourse', 'miniCourseUser'));
        } catch (\Throwable $th) {
            Log::error('❌ Error al cargar módulos del mini curso', [
                'mini_course_id' => $id,
                'error' => $th->getMessage()
            ]);
        
            abort(500, 'Error al cargar el mini curso');
        }
    }

}
