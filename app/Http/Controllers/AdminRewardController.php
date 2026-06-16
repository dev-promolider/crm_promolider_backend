<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminRewardController extends Controller
{
    /**
     * Listar todos los premios (admin)
     */
    public function index()
    {
        try {
            $rewards = Reward::withTrashed()
                ->withCount('redemptions')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rewards
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar premios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los premios'
            ], 500);
        }
    }

    /**
     * Crear nuevo premio
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'required|string',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reward = Reward::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Premio creado exitosamente',
                'data' => $reward
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear premio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el premio'
            ], 500);
        }
    }

    /**
     * Actualizar premio
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'cost' => 'sometimes|required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'sometimes|required|string',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reward = Reward::findOrFail($id);
            $reward->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Premio actualizado exitosamente',
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar premio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el premio'
            ], 500);
        }
    }

    /**
     * Eliminar premio (soft delete)
     */
    public function destroy($id)
    {
        try {
            $reward = Reward::find($id);

            if (!$reward) {
                return response()->json([
                    'success' => false,
                    'message' => 'El premio no existe o ya fue eliminado'
                ], 404);
            }

            $reward->delete();

            return response()->json([
                'success' => true,
                'message' => 'Premio eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar premio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el premio'
            ], 500);
        }
    }


    /**
     * Restaurar premio eliminado (soft delete)
     */
    public function restore($id)
    {
        try {
            $reward = Reward::withTrashed()->findOrFail($id);
            
            if (!$reward->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El premio no está eliminado'
                ], 400);
            }
            
            $reward->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Premio restaurado exitosamente',
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar premio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el premio'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de premios
     */
    public function stats()
    {
        try {
            $stats = [
                'total_rewards' => Reward::count(),
                'active_rewards' => Reward::active()->count(),
                'total_redemptions' => RewardRedemption::count(),
                'total_credits_used' => RewardRedemption::sum('cost'),
                'pending_redemptions' => RewardRedemption::pending()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las estadísticas'
            ], 500);
        }
    }

    /**
     * Listar todos los canjes
     */
    public function redemptions(Request $request)
    {
        try {
            $query = RewardRedemption::with(['user', 'reward', 'processor']);

            // Filtros opcionales
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $redemptions = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $redemptions
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar canjes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los canjes'
            ], 500);
        }
    }

    /**
     * Procesar un canje
     */
    public function processRedemption(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processed,cancelled',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $redemption = RewardRedemption::findOrFail($id);

            // Si el canje se cancela, devolver los créditos al usuario
            if ($request->status === 'cancelled' && $redemption->status === 'pending') {
                $user = $redemption->user;
                $user->credits += $redemption->cost;
                $user->save();

                // Devolver stock si aplica
                $reward = $redemption->reward;
                if ($reward->stock !== null) {
                    $reward->increment('stock');
                }
            }

            $redemption->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Canje procesado exitosamente',
                'data' => $redemption->load(['user', 'reward'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar canje: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el canje'
            ], 500);
        }
    }
}