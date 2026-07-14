<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SharedLink;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SponsorLinkController extends Controller
{
    /**
     * GET /marketing/sponsor-links/remaining-time
     * Obtiene el tiempo restante del enlace de patrocinio activo del usuario autenticado.
     */
    public function remainingTime(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $now = Carbon::now('UTC');

            $sponsorLink = SharedLink::where('user_id', $userId)
                ->where('fecha_fin', '>', $now)
                ->where('estado', true)
                ->latest('created_at')
                ->first();

            if (!$sponsorLink) {
                return response()->json([
                    'tiempoRestanteEnSegundos' => 0,
                    'fechaFin' => null,
                ]);
            }

            $endDate = Carbon::parse($sponsorLink->fecha_fin);
            $remainingSeconds = max(0, $now->diffInSeconds($endDate, false));

            return response()->json([
                'tiempoRestanteEnSegundos' => (int) $remainingSeconds,
                'fechaFin' => $endDate->toIso8601String(),
                'url' => $sponsorLink->url,
                'activo' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tiempo restante: ' . $e->getMessage());
            return response()->json(['tiempoRestanteEnSegundos' => 0, 'fechaFin' => null], 500);
        }
    }

    /**
     * GET /marketing/sponsor-links/user-info/{username}
     * Retorna información del usuario + su enlace de patrocinio activo (público).
     */
    public function userInfo(string $username): JsonResponse
    {
        try {
            $user = User::where('username', $username)->first();

            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $now = Carbon::now('UTC');
            $activeLink = SharedLink::where('user_id', $user->id)
                ->where('fecha_fin', '>', $now)
                ->where('estado', true)
                ->latest('created_at')
                ->first();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'active_link' => $activeLink ? [
                    'id' => $activeLink->id,
                    'url' => $activeLink->url,
                    'fecha_inicio' => $activeLink->fecha_inicio,
                    'fecha_fin' => $activeLink->fecha_fin,
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener información de usuario: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
