<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Preregistro;
use App\Models\SponsorLink;
use App\Models\UnverifiedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShareLinkController extends Controller
{
    public function index()
    {
        return view('content.config.share-link');
    }

    public function Add(Request $request)
    {
        try {
            $userId = auth()->id();
            $userTimezone = auth()->user()->timezone ?? 'UTC';
            
            Log::info('Creando enlace para usuario', [
                'user_id' => $userId,
                'timezone' => $userTimezone
            ]);

            // 1. Eliminar enlaces EXPIRADOS del usuario actual
            $this->deleteExpiredLinksForUser($userId);

            // 2. Verificar si el usuario ya tiene un enlace activo
            $existingLink = SponsorLink::where('user_id', $userId)
                ->where('fecha_fin', '>', Carbon::now())
                ->where('estado', true)
                ->first();

            if ($existingLink) {
                $fechaFin = Carbon::parse($existingLink->fecha_fin);
                
                Log::info('Usuario ya tiene enlace activo', [
                    'user_id' => $userId,
                    'link_id' => $existingLink->id,
                    'fecha_fin' => $fechaFin->toIso8601String()
                ]);
            
                return response()->json([
                    'resource' => $existingLink,
                    'tiempoRestanteEnSegundos' => max(0, Carbon::now()->diffInSeconds($fechaFin, false)),
                    'fechaFin' => $fechaFin->toIso8601String(),
                    'message' => 'Ya tienes un enlace activo'
                ], 200);
            }

            // 3. Crear nuevo enlace (SIEMPRE en UTC para consistencia)
            $now = Carbon::now('UTC');
            $user = auth()->user();
            $uniqueUrl = $this->generateUniqueUrl($user);

            $sponsorLink = new SponsorLink();
            $sponsorLink->user_id = $userId;
            $sponsorLink->url = $uniqueUrl;
            $sponsorLink->estado = true;
            $sponsorLink->fecha_inicio = $now;
            $sponsorLink->fecha_fin = $now->copy()->addHours(5); // Exactamente 5 horas

            Log::info('Sponsor Link creado', [
                'user_id' => $userId,
                'fecha_inicio' => $sponsorLink->fecha_inicio->toIso8601String(),
                'fecha_fin' => $sponsorLink->fecha_fin->toIso8601String(),
                'url' => $sponsorLink->url,
            ]);

            if ($sponsorLink->save()) {
                $tiempoRestanteEnSegundos = Carbon::now('UTC')->diffInSeconds($sponsorLink->fecha_fin, false);

                Log::info('Enlace guardado exitosamente', [
                    'link_id' => $sponsorLink->id,
                    'tiempoRestanteEnSegundos' => $tiempoRestanteEnSegundos
                ]);
            
                return response()->json([
                    'resource' => $sponsorLink,
                    'tiempoRestanteEnSegundos' => max(0, $tiempoRestanteEnSegundos),
                    'fechaFin' => $sponsorLink->fecha_fin->toIso8601String()
                ], 200);
            }
            
            return response()->json(['error' => 'Error al guardar el enlace'], 400);

        } catch (\Exception $e) {
            Log::error('Error al crear enlace', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    public function validateRegistrationLink(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $code = $request->input('code');
            $hash = $request->input('hash');

            Log::info('Validando enlace de registro', [
                'user_id' => $userId,
                'code' => $code,
                'hash' => $hash,
                'current_time' => Carbon::now('UTC')->toIso8601String()
            ]);

            // Buscar al usuario
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Buscar el enlace activo más reciente para este usuario
            $now = Carbon::now('UTC');
            $sponsorLink = SponsorLink::where('user_id', $userId)
                ->where('estado', true)
                ->where('fecha_fin', '>', $now)
                ->latest('created_at')
                ->first();

            if (!$sponsorLink) {
                Log::warning('Enlace no encontrado o expirado', [
                    'user_id' => $userId,
                    'current_time' => $now->toIso8601String()
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'El enlace ha expirado o no es válido. Por favor, solicita un nuevo enlace.'
                ], 200);
            }

            // Verificar que el código coincida con la URL generada
            $urlParts = explode('/', $sponsorLink->url);
            $storedCode = $urlParts[count($urlParts) - 2] ?? null;
            $storedHash = $urlParts[count($urlParts) - 1] ?? null;

            if ($storedCode !== $code || $storedHash !== $hash) {
                Log::warning('Código o hash no coincide', [
                    'stored_code' => $storedCode,
                    'received_code' => $code,
                    'stored_hash' => $storedHash,
                    'received_hash' => $hash
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'El enlace no es válido. Verifica que hayas copiado la URL completa.'
                ], 200);
            }

            // Verificar si el enlace está por expirar (menos de 1 minuto restante)
            $tiempoRestante = $now->diffInSeconds($sponsorLink->fecha_fin, false);
            
            if ($tiempoRestante <= 0) {
                // El enlace acaba de expirar
                $sponsorLink->estado = false;
                $sponsorLink->save();

                Log::info('Enlace expirado durante validación', [
                    'link_id' => $sponsorLink->id,
                    'user_id' => $userId
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'El enlace ha expirado. Por favor, solicita un nuevo enlace.'
                ], 200);
            }

            // El enlace es válido
            Log::info('Enlace válido', [
                'link_id' => $sponsorLink->id,
                'tiempo_restante' => $tiempoRestante
            ]);

            return response()->json([
                'valid' => true,
                'message' => 'Enlace válido',
                'tiempo_restante' => $tiempoRestante,
                'fecha_fin' => $sponsorLink->fecha_fin->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error validando enlace de registro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Error al validar el enlace'
            ], 500);
        }
    }

    /**
     * Elimina solo los enlaces expirados de un usuario específico
     */
    private function deleteExpiredLinksForUser($userId)
    {
        try {
            $now = Carbon::now('UTC');
            
            // Primero, marcar enlaces como inactivos
            $updated = SponsorLink::where('user_id', $userId)
                ->where('fecha_fin', '<', $now)
                ->where('estado', true)
                ->update(['estado' => false]);

            // Luego eliminar enlaces muy antiguos (más de 24 horas expirados)
            $deletedCount = SponsorLink::where('user_id', $userId)
                ->where('fecha_fin', '<', $now->copy()->subHours(24))
                ->delete();
                
            Log::info('Enlaces expirados procesados', [
                'user_id' => $userId,
                'updated_count' => $updated,
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            Log::warning('Error al procesar enlaces expirados', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Genera una URL única para el usuario
     */
    private function generateUniqueUrl($user)
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $timestamp = time();
        $randomString = substr(md5($user->id . $timestamp . uniqid()), 0, 8);
        
        return "{$baseUrl}/register/{$user->id}/{$timestamp}/{$randomString}";
    }

    public function obtenerTiempoRestante()
    {
        $userId = auth()->id();
        
        // Buscar enlace activo más reciente
        $now = Carbon::now('UTC');
        $sponsorLink = SponsorLink::where('user_id', $userId)
            ->where('fecha_fin', '>', $now)
            ->where('estado', true)
            ->latest('created_at')
            ->first();

        Log::info('Consultando tiempo restante', [
            'user_id' => $userId,
            'current_time' => $now->toIso8601String(),
            'has_link' => !is_null($sponsorLink)
        ]);

        if (!$sponsorLink) {
            return response()->json([
                'tiempoRestanteEnSegundos' => 0,
                'fechaFin' => null
            ]);
        }

        $fechaFin = Carbon::parse($sponsorLink->fecha_fin);
        $tiempoRestanteEnSegundos = $now->diffInSeconds($fechaFin, false);

        // Si el tiempo es negativo o cero, marcar como inactivo
        if ($tiempoRestanteEnSegundos <= 0) {
            $sponsorLink->estado = false;
            $sponsorLink->save();

            return response()->json([
                'tiempoRestanteEnSegundos' => 0,
                'fechaFin' => null
            ]);
        }

        return response()->json([
            'tiempoRestanteEnSegundos' => $tiempoRestanteEnSegundos,
            'fechaFin' => $fechaFin->toIso8601String()
        ]);
    }

    public function Delete($id)
    {
        try {
            $shareLink = SponsorLink::findOrFail($id);
            
            if ($shareLink->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este enlace',
                    'state' => 403
                ], 403);
            }

            if ($shareLink->delete()) {
                Log::info('Enlace eliminado', [
                    'user_id' => auth()->id(),
                    'link_id' => $id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registro eliminado con éxito',
                    'state' => 200
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar',
                'state' => 400
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error al eliminar enlace', [
                'user_id' => auth()->id(),
                'link_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'state' => 500
            ], 500);
        }
    }

    /**
     * Retorna los referidos (registro pagado) y preregistros (pendientes)
     * del usuario autenticado.
     */
    public function referralsByUsername($username)
    {
        try {
            $userId = auth()->id();

            if (! $userId) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $authUser = auth()->user();

            // ──────────────────────────────────────────────────────────────────
            // 1. Directos pagados (origen: registro)
            //    Usuarios reales que se registraron con este referidor.
            // ──────────────────────────────────────────────────────────────────
            $directs = User::where('id_referrer_sponsor', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($u) => [
                    'id'             => $u->id,
                    'nombre'         => trim(($u->name ?? '') . ' ' . ($u->last_name ?? '')),
                    'lado'           => ($u->position ?? 0) == 0 ? 'izquierda' : 'derecha',
                    'whatsapp'       => $u->phone ?? '',
                    'correo'         => $u->email ?? '',
                    'fecha_registro' => $u->created_at ? $u->created_at->toDateTimeString() : null,
                    'origen'         => 'registro',
                    'pago_estado'    => 'pagado',
                ]);

            // ──────────────────────────────────────────────────────────────────
            // 2. Preregistros que iniciaron pago (origen: preregistro, pendiente)
            //    UnverifiedUser almacena en su JSON data el id_referrer_sponsor.
            // ──────────────────────────────────────────────────────────────────
            $preregistrosConPago = collect();
            $unverifiedRows = UnverifiedUser::whereRaw(
                'JSON_EXTRACT(data, "$.id_referrer_sponsor") = ?',
                [(string) $userId]
            )->get();

            $preregistroIdsConPago = [];

            foreach ($unverifiedRows as $uv) {
                $uvData = is_string($uv->data) ? json_decode($uv->data, true) : $uv->data;
                $preregistroId = $uvData['preregistro_id'] ?? null;

                if (! $preregistroId) {
                    continue;
                }

                $preregistroIdsConPago[] = $preregistroId;

                $preregistro = Preregistro::find($preregistroId);
                if (! $preregistro) {
                    continue;
                }

                $preregistrosConPago->push([
                    'id'             => $preregistro->id,
                    'nombre'         => trim(($preregistro->nombres ?? '') . ' ' . ($preregistro->apellidos ?? '')),
                    'lado'           => isset($uvData['binary_position'])
                        ? ($uvData['binary_position'] == 0 ? 'izquierda' : 'derecha')
                        : ($preregistro->lado ?? '—'),
                    'whatsapp'       => $preregistro->whatsapp ?? $uvData['phone'] ?? '',
                    'correo'         => $preregistro->correo ?? $uvData['email'] ?? '',
                    'fecha_registro' => $preregistro->created_at
                        ? $preregistro->created_at->toDateTimeString()
                        : null,
                    'origen'         => 'preregistro',
                    'pago_estado'    => 'pendiente',
                ]);
            }

            // ──────────────────────────────────────────────────────────────────
            // 3. Preregistros sin iniciar pago (origen: preregistro, sin_pago)
            //    Son los que solo llenaron el formulario de landing.
            // ──────────────────────────────────────────────────────────────────
            $preregistrosSinPago = Preregistro::where('referrer_username', $authUser->username)
                ->whereNotIn('id', $preregistroIdsConPago)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($p) => [
                    'id'             => $p->id,
                    'nombre'         => trim(($p->nombres ?? '') . ' ' . ($p->apellidos ?? '')),
                    'lado'           => $p->lado ?? '—',
                    'whatsapp'       => $p->whatsapp ?? '',
                    'correo'         => $p->correo ?? '',
                    'fecha_registro' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                    'origen'         => 'preregistro',
                    'pago_estado'    => 'sin_pago',
                ]);

            // ─── Combinar resultados ──────────────────────────────────────────
            $allRows = $directs
                ->concat($preregistrosConPago)
                ->concat($preregistrosSinPago);

            return response()->json([
                'rows'    => $allRows->values()->all(),
                'summary' => [
                    'total_registro'         => $directs->count(),
                    'total_preregistro_pago' => $preregistrosConPago->count(),
                    'total_preregistro'      => $preregistrosSinPago->count(),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        } catch (\Exception $e) {
            Log::error('Error en referralsByUsername', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function retornarVista($username)
    {
        $user = User::where('username', $username)->first();
        
        if (is_null($user)) {
             return response()->json(['error' => 'User not found'], 404);
        }

        $now = Carbon::now('UTC');
        $link = SponsorLink::where('user_id', $user->id)
            ->where('fecha_fin', '>', $now)
            ->where('estado', true)
            ->latest()
            ->first();

        if (is_null($link)) {
            $link = 0;
        }

        $this->authorize('view', $user);
        return response()->json([$user, $link], 200);
    }

    /**
     * Limpieza manual de enlaces expirados (para cron job)
     */
    public function cleanupExpiredLinks()
    {
        try {
            $now = Carbon::now('UTC');
            
            // Marcar como inactivos
            $updated = SponsorLink::where('fecha_fin', '<', $now)
                ->where('estado', true)
                ->update(['estado' => false]);

            // Eliminar enlaces muy antiguos
            $deletedCount = SponsorLink::where('fecha_fin', '<', $now->copy()->subHours(24))
                ->delete();
            
            Log::info('Limpieza de enlaces expirados', [
                'updated_count' => $updated,
                'deleted_count' => $deletedCount
            ]);

            return response()->json([
                'message' => 'Limpieza completada',
                'updated_count' => $updated,
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error en limpieza de enlaces', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Error en limpieza'], 500);
        }
    }
}