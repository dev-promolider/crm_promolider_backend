<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AccountType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    /**
     * Obtener todos los premios disponibles
     */
    public function index()
    {
        try {
            $rewards = Reward::available()
                ->orderBy('cost', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rewards
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener premios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los premios'
            ], 500);
        }
    }

    /**
     * Obtener créditos disponibles del usuario
     */
    public function getCredits()
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'credits' => $user->credits ?? 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener créditos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los créditos'
            ], 500);
        }
    }

    /**
     * Canjear un premio
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|exists:rewards,id'
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $reward = Reward::findOrFail($request->reward_id);

            // Validar que el premio esté activo
            if (!$reward->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este premio no está disponible'
                ], 400);
            }

            // Validar que haya stock disponible
            if (!$reward->hasStock()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este premio está agotado'
                ], 400);
            }

            // Validar que el usuario tenga suficientes créditos
            if ($user->credits < $reward->cost) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes suficientes créditos para este premio'
                ], 400);
            }

            // Descontar créditos del usuario
            $user->credits -= $reward->cost;
            $user->save();

            // Decrementar stock del premio
            $reward->decrementStock();

            // Crear registro de canje
            $redemption = RewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'cost' => $reward->cost,
                'status' => 'pending'
            ]);
            
            // 🚨 PREMIO ESPECIAL: Membresía School (ID = 1)
            if ($reward->id == 1) {
            
                $result = $this->processSchoolMembershipRedemption($redemption);
            
                // 🔥 IMPORTANTE: commit global
                DB::commit();
            
                return $result;
            }
            
            // Caso normal
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Premio canjeado exitosamente',
                'data' => [
                    'redemption' => $redemption,
                    'remaining_credits' => $user->credits
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al canjear premio: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el canje. Por favor intenta nuevamente.'
            ], 500);
        }
    }

    /**
     * Obtener historial de canjes del usuario
     */
    public function myRedemptions()
    {
        try {
            $user = Auth::user();
            
            $redemptions = RewardRedemption::with('reward')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $redemptions
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener historial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el historial'
            ], 500);
        }
    }

    private function processSchoolMembershipRedemption(RewardRedemption $redemption)
    {
        $user = $redemption->user;
    
        try {
            $membership_id = 2; // School
            $account_type = AccountType::find($membership_id);
        
            if (!$account_type) {
                return response()->json([
                    'success' => false,
                    'message' => 'La membresía School no existe'
                ], 404);
            }
        
            // CALCULAR FECHAS
            if ($user->expiration_membership_date) {
                $newExpirationMembership = Carbon::parse($user->expiration_membership_date)->addDays(365);
            } else {
                $newExpirationMembership = now()->addDays(365);
            }
        
            if ($user->expiration_date) {
                $newExpirationOPC = Carbon::parse($user->expiration_date)->addDays(30);
            } else {
                $newExpirationOPC = now()->addDays(30);
            }
        
            // ACTUALIZAR USUARIO
            $previous_id = $user->id_account_type;
        
            $user->id_account_type = $membership_id;
            $user->expiration_membership_date = $newExpirationMembership;
            $user->expiration_date = $newExpirationOPC;
            $user->save();
        
            // Marcar canje como procesado autom.
            $redemption->status = 'processed';
            $redemption->processed_by = 1;
            $redemption->processed_at = now();
            $redemption->notes = "Actualización automática a School por canje de premio ID 1";
            $redemption->save();
        
            Log::info("Canje especial realizado correctamente", [
                'user_id' => $user->id,
                'previous_membership' => $previous_id,
                'new_membership' => $membership_id,
                'redemption_id' => $redemption->id,
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Membresía School activada correctamente mediante canje',
                'membership_id' => $membership_id,
            ]);
        
        } catch (\Exception $e) {
        
            Log::error("Error en canje especial School: ".$e->getMessage(), [
                'user_id' => $user->id ?? null,
                'redemption_id' => $redemption->id,
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la membresía School'
            ], 500);
        }
    }

}