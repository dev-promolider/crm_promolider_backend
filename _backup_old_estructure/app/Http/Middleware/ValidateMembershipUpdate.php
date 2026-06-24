<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateMembershipUpdate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo aplicar a rutas de actualización de membresía
        if ($request->is('pay/membership-wallet')) {
            $user = auth()->user();
            $membershipId = $request->membership_id;

            // Validar que el parámetro existe
            if (!$membershipId) {
                return response()->json(['error' => 'ID de membresía requerido'], 400);
            }

            // Validar que el membership_id sea un número válido
            if (!is_numeric($membershipId) || $membershipId < 1 || $membershipId > 8) {
                Log::warning('ID de membresía inválido', [
                    'user_id' => $user->id,
                    'attempted_membership' => $membershipId,
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json(['error' => 'ID de membresía inválido'], 400);
            }

            // Rate limiting: máximo 5 intentos por hora por usuario
            $rateLimitKey = 'membership_attempts_' . $user->id;
            $attempts = cache()->get($rateLimitKey, 0);
            
            if ($attempts >= 5) {
                Log::warning('Rate limit excedido para actualización de membresía', [
                    'user_id' => $user->id,
                    'attempts' => $attempts
                ]);
                
                return response()->json([
                    'error' => 'Demasiados intentos. Intenta nuevamente en una hora.'
                ], 429);
            }
            
            // Incrementar contador de intentos
            cache()->put($rateLimitKey, $attempts + 1, now()->addHour());
        }

        return $next($request);
    }
}