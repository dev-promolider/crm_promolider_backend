<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SponsorLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateRegistrationLink
{
    public function handle(Request $request, Closure $next)
    {
        // Para rutas GET (mostrar formulario)
        if ($request->isMethod('GET')) {
            $id = $request->route('id');
            $code = $request->route('code');
            
            if (!$this->isValidLink($id, $code)) {
                return redirect('/login')->with('error', 'El enlace de registro no es válido o ha expirado.');
            }
        }
        
        // Para rutas POST (envío de formulario)
        if ($request->isMethod('POST')) {
            $referrerId = $request->input('id_referrer_sponsor');
            
            // Buscar el link activo para este usuario
            $activeLink = SponsorLink::where('user_id', $referrerId)
                ->where('estado', true)
                ->where('fecha_fin', '>', Carbon::now())
                ->latest()
                ->first();
            
            if (!$activeLink) {
                return response()->json([
                    'errors' => [
                        'general' => ['El enlace de registro ha expirado o no es válido.']
                    ]
                ], 422);
            }
        }
        
        return $next($request);
    }
    
    private function isValidLink($userId, $code)
    {
        try {
            $appUrl = env('APP_URL') . 'register/' . $userId . '/' . $code;
            
            $sponsorLink = SponsorLink::where('url', $appUrl)
                ->where('user_id', $userId)
                ->first();
            
            if (!$sponsorLink) {
                Log::warning('Link no encontrado', ['url' => $appUrl]);
                return false;
            }
            
            if (Carbon::now()->gt($sponsorLink->fecha_fin)) {
                Log::warning('Link expirado', [
                    'url' => $appUrl,
                    'fecha_fin' => $sponsorLink->fecha_fin,
                    'now' => Carbon::now()
                ]);
                return false;
            }
            
            if (!$sponsorLink->estado) {
                Log::warning('Link suspendido', ['url' => $appUrl]);
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error validando link', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'code' => $code
            ]);
            return false;
        }
    }
}