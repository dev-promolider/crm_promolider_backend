<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpcPurchaseRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('pay/opc-wallet')) {
            $user = auth()->user();
            $userId = $user->id;
            
            // 1. RATE LIMITING POR MINUTO
            $minuteKey = 'opc_minute_' . $userId . '_' . now()->format('Y-m-d-H-i');
            $minuteCount = cache()->get($minuteKey, 0);
            
            if ($minuteCount >= 2) { // Máximo 2 compras por minuto
                Log::warning('Rate limit por minuto excedido - OPC', [
                    'user_id' => $userId,
                    'attempts' => $minuteCount,
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'error' => 'Demasiadas compras seguidas',
                    'limite' => 'Máximo 2 compras por minuto',
                    'espera' => '60 segundos'
                ], 429);
            }
            
            // 2. RATE LIMITING POR HORA
            $hourKey = 'opc_hour_' . $userId . '_' . now()->format('Y-m-d-H');
            $hourCount = cache()->get($hourKey, 0);
            
            if ($hourCount >= 10) { // Máximo 10 compras por hora
                Log::warning('Rate limit por hora excedido - OPC', [
                    'user_id' => $userId,
                    'attempts' => $hourCount,
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'error' => 'Límite de compras por hora alcanzado',
                    'limite' => 'Máximo 10 compras por hora',
                    'espera' => 'Intenta en una hora'
                ], 429);
            }
            
            // 3. DETECCIÓN DE PATRONES SOSPECHOSOS
            $suspiciousKey = 'opc_suspicious_' . $userId;
            $recentRequests = cache()->get($suspiciousKey, []);
            $recentRequests[] = now()->timestamp;
            
            // Mantener solo los últimos 10 segundos de requests
            $recentRequests = array_filter($recentRequests, function($timestamp) {
                return $timestamp > (now()->timestamp - 10);
            });
            
            // Si hay más de 3 requests en 10 segundos, es sospechoso
            if (count($recentRequests) > 3) {
                Log::critical('PATRÓN SOSPECHOSO DETECTADO - Compras OPC automáticas', [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'requests_in_10_seconds' => count($recentRequests),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamps' => $recentRequests
                ]);
                
                // Bloquear temporalmente (15 minutos)
                cache()->put('opc_blocked_' . $userId, true, now()->addMinutes(15));
                
                return response()->json([
                    'error' => 'Actividad sospechosa detectada',
                    'message' => 'Tu cuenta ha sido bloqueada temporalmente por seguridad',
                    'duracion' => '15 minutos'
                ], 423); // 423 = Locked
            }
            
            cache()->put($suspiciousKey, $recentRequests, now()->addMinutes(1));
            
            // 4. VERIFICAR SI ESTÁ BLOQUEADO
            if (cache()->has('opc_blocked_' . $userId)) {
                return response()->json([
                    'error' => 'Cuenta bloqueada temporalmente',
                    'message' => 'Contacta al soporte si crees que esto es un error'
                ], 423);
            }
            
            // 5. INCREMENTAR CONTADORES
            cache()->put($minuteKey, $minuteCount + 1, now()->addMinutes(2));
            cache()->put($hourKey, $hourCount + 1, now()->addHours(2));
            
            // 6. LOG DE ACTIVIDAD NORMAL
            Log::info('Solicitud OPC autorizada', [
                'user_id' => $userId,
                'minute_count' => $minuteCount + 1,
                'hour_count' => $hourCount + 1
            ]);
        }

        return $next($request);
    }
}