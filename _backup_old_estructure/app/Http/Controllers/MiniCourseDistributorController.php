<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MiniCourse;
use App\Models\MiniCourseDistributor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MiniCourseDistributorController extends Controller
{
    /**
     * Crear enlace de invitación para un mini curso
     */
    public function createInvitationLink($id)
    {
        $user = Auth::user();
        $random = Str::random(10);
        $code = $user->id . $random;

        $invitation = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->first();

        if ($invitation) {
            $invitation->update([
                'code' => $code,
                'expires_at' => now()->addDays(7),
            ]);
        }

        return response()->json([
            'link' => url("/mini-course/register?invitation_code={$code}"),
        ]);
    }

    /**
     * Verificar si existe una invitación para el mini curso
     */
    public function checkInvitation($id)
    {
        $user = Auth::user();

        $existInvitation = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->where('code', '!=', 0)
            ->exists();

        $data = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->where('code', '!=', 0)
            ->first();

        $invitationLink = $data 
            ? url("/mini-course/register?invitation_code={$data->code}")
            : null;

        return response()->json([
            'existInvitation' => $existInvitation,
            'invitationLink' => $invitationLink
        ]);
    }

    /**
     * Comprar/acceder a un mini curso
     */
    public function purchase($miniCourseId)
    {
        try {
            $user = Auth::user();
            
            $miniCourse = MiniCourse::find($miniCourseId);
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado'], 404);
            }

            // Verificar si ya tiene acceso
            $alreadyPurchased = MiniCourseDistributor::where('user_id', $user->id)
                ->where('mini_course_id', $miniCourseId)
                ->exists();

            if ($alreadyPurchased) {
                return response()->json([
                    'message' => 'Ya tienes acceso a este mini curso',
                    'isPurchased' => true, // Agregado para consistencia
                    'mini_course_id' => $miniCourseId
                ], 200);
            }

            // Crear acceso al mini curso
            MiniCourseDistributor::create([
                'user_id' => $user->id,
                'mini_course_id' => $miniCourseId,
                'code' => Str::uuid(),
                'expires_at' => now()->addDays(30), // Acceso por 30 días
            ]);

            Log::info('Acceso a mini curso concedido', [
                'user_id' => $user->id,
                'mini_course_id' => $miniCourseId
            ]);

            return response()->json([
                'message' => 'Acceso al mini curso concedido exitosamente',
                'isPurchased' => true, // Agregado para consistencia
                'mini_course_id' => $miniCourseId,
                'mini_course_title' => $miniCourse->title
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error al comprar mini curso', [
                'mini_course_id' => $miniCourseId,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al procesar el acceso al mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar si el usuario tiene acceso al mini curso
     */
    public function checkPurchase($miniCourseId)
    {
        $user = auth()->user();
        $mini_course = MiniCourse::find($miniCourseId);
        
        if (!$mini_course) {
            return response()->json(['message' => 'Mini Curso no encontrado'], 404);
        }

        $hasPurchased = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $miniCourseId)
            ->exists();

        return response()->json([
            'isPurchased' => $hasPurchased
        ]);
    }

    /**
     * Listar usuarios con acceso a un mini curso específico
     */
    public function listCourseUsers($miniCourseId)
    {
        try {
            $user = Auth::user();
            
            // Verificar que el mini curso existe y pertenece al usuario
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', $user->id)
                ->first();

            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado o sin permisos'
                ], 404);
            }

            $distributors = MiniCourseDistributor::with('user:id,name,email')
                ->where('mini_course_id', $miniCourseId)
                ->where('user_id', '!=', $user->id) // Excluir al propietario
                ->get();

            $usersData = $distributors->map(function ($distributor) {
                return [
                    'user_id' => $distributor->user->id,
                    'user_name' => $distributor->user->name,
                    'user_email' => $distributor->user->email,
                    'access_granted_at' => $distributor->created_at,
                    'expires_at' => $distributor->expires_at,
                    'is_active' => !$distributor->expires_at || $distributor->expires_at > now()
                ];
            });

            return response()->json([
                'message' => 'Lista de usuarios obtenida correctamente',
                'data' => [
                    'mini_course_id' => $miniCourseId,
                    'mini_course_title' => $miniCourse->title,
                    'total_users' => $usersData->count(),
                    'users' => $usersData
                ]
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error al listar usuarios del mini curso', [
                'mini_course_id' => $miniCourseId,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al obtener la lista de usuarios',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Revocar acceso a un usuario específico
     */
    public function revokeAccess($miniCourseId, $userId)
    {
        try {
            $user = Auth::user();
            
            // Verificar que el mini curso existe y pertenece al usuario
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', $user->id)
                ->first();

            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado o sin permisos'
                ], 404);
            }

            // Buscar y eliminar el acceso
            $distributor = MiniCourseDistributor::where('mini_course_id', $miniCourseId)
                ->where('user_id', $userId)
                ->first();

            if (!$distributor) {
                return response()->json([
                    'message' => 'El usuario no tiene acceso a este mini curso'
                ], 404);
            }

            $distributor->delete();

            Log::info('Acceso revocado', [
                'owner_id' => $user->id,
                'mini_course_id' => $miniCourseId,
                'revoked_user_id' => $userId
            ]);

            return response()->json([
                'message' => 'Acceso revocado correctamente',
                'data' => [
                    'mini_course_id' => $miniCourseId,
                    'revoked_user_id' => $userId
                ]
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error al revocar acceso', [
                'mini_course_id' => $miniCourseId,
                'user_id' => Auth::id(),
                'target_user_id' => $userId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al revocar el acceso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar mini cursos a los que el usuario tiene acceso (como estudiante)
     */
    public function myAccessibleCourses()
    {
        try {
            $user = Auth::user();

            $accessibleCourses = MiniCourseDistributor::with([
                'miniCourse:id,title,description,level,duration,status',
                'miniCourse.category:id,name',
                'miniCourse.user:id,name'
            ])
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();

            $coursesData = $accessibleCourses->map(function ($distributor) {
                return [
                    'mini_course_id' => $distributor->miniCourse->id,
                    'title' => $distributor->miniCourse->title,
                    'description' => $distributor->miniCourse->description,
                    'level' => $distributor->miniCourse->level,
                    'duration' => $distributor->miniCourse->duration,
                    'status' => $distributor->miniCourse->status,
                    'category' => $distributor->miniCourse->category ? $distributor->miniCourse->category->name : null,
                    'instructor' => $distributor->miniCourse->user->name,
                    'access_granted_at' => $distributor->created_at,
                    'expires_at' => $distributor->expires_at
                ];
            });

            return response()->json([
                'message' => 'Mini cursos accesibles obtenidos correctamente',
                'data' => [
                    'total_courses' => $coursesData->count(),
                    'courses' => $coursesData
                ]
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error al obtener mini cursos accesibles', [
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al obtener los mini cursos accesibles',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Registrar usuario con código de invitación
     */
    public function registerWithInvitation(Request $request)
    {
        try {
            $validated = $request->validate([
                'invitation_code' => 'required|string'
            ]);

            // Buscar la invitación
            $invitation = MiniCourseDistributor::where('code', $request->invitation_code)
                ->where('expires_at', '>', now())
                ->first();

            if (!$invitation) {
                return response()->json([
                    'message' => 'Código de invitación inválido o expirado'
                ], 404);
            }

            $user = Auth::user();
            $miniCourse = MiniCourse::find($invitation->mini_course_id);

            // Verificar si ya tiene acceso
            $alreadyHasAccess = MiniCourseDistributor::where('user_id', $user->id)
                ->where('mini_course_id', $invitation->mini_course_id)
                ->exists();

            if ($alreadyHasAccess) {
                return response()->json([
                    'message' => 'Ya tienes acceso a este mini curso',
                    'data' => [
                        'mini_course_id' => $invitation->mini_course_id,
                        'mini_course_title' => $miniCourse->title
                    ]
                ], 200);
            }

            // Crear nuevo acceso
            MiniCourseDistributor::create([
                'user_id' => $user->id,
                'mini_course_id' => $invitation->mini_course_id,
                'code' => Str::uuid(),
                'expires_at' => now()->addDays(30),
            ]);

            Log::info('Usuario registrado con código de invitación', [
                'user_id' => $user->id,
                'mini_course_id' => $invitation->mini_course_id,
                'invitation_code' => $request->invitation_code
            ]);

            return response()->json([
                'message' => 'Te has registrado exitosamente al mini curso',
                'data' => [
                    'mini_course_id' => $invitation->mini_course_id,
                    'mini_course_title' => $miniCourse->title,
                    'instructor' => $miniCourse->user->name ?? 'Instructor'
                ]
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error al registrar con código de invitación', [
                'user_id' => Auth::id(),
                'invitation_code' => $request->invitation_code ?? null,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al procesar el registro con invitación',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}