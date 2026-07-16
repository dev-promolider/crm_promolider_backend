<?php
namespace Promolider\Application\Wallet\UseCases\OPC;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Product;
use App\Models\Payment;
use Carbon\Carbon;
use App\Models\Classified;
use App\Models\Point;

class PurchaseOpcWithWalletUseCase
{
    public function execute(int $userId, int $cuotasRequested): array
    {
        if ($cuotasRequested < 1) {
            throw new Exception("Debes pagar al menos 1 cuota.", 422);
        }

        try {
            DB::beginTransaction();

            $user = User::where('id', $userId)->lockForUpdate()->first();
            if (!$user) {
                throw new Exception("Usuario no encontrado", 404);
            }

            if (now()->greaterThan($user->expiration_membership_date)) {
                throw new Exception("Tu membresía anual ha vencido. Por favor renueva tu membresía para reintegrarte al sistema.", 403);
            }

            $product = Product::where('name', 'opc')
                ->where('account_type_id', $user->id_account_type)
                ->first();

            if (!$product) {
                throw new Exception("No existe un producto OPC asociado a tu membresía.", 404);
            }

            $amountPerQuota = $product->price;
            $totalAmount = $amountPerQuota * $cuotasRequested;

            // Obtener Billetera
            $wallet = DB::table('wallet')->where('user_id', $user->id)->first();
            if (!$wallet) {
                throw new Exception("Billetera no encontrada para este usuario.", 404);
            }

            // Calcular saldo de billetera
            $movements = DB::table('wallet_movements')->where('wallet_id', $wallet->id)->get();
            $balance = 0;
            foreach ($movements as $m) {
                $balance += $m->amount;
            }

            if ($balance < ($totalAmount - 0.01)) {
                throw new Exception("Saldo insuficiente en la billetera. Saldo actual: $" . number_format($balance, 2) . ", requerido: $" . number_format($totalAmount, 2));
            }

            // Descontar saldo
            DB::table('wallet_movements')->insert([
                'wallet_id' => $wallet->id,
                'id_receiver' => $user->id,
                'id_payment' => null,
                'type' => 0, // Débito
                'amount' => -$totalAmount,
                'status' => 1, // Aprobado
                'reason' => "Mantenimiento/Pago de OPC ($cuotasRequested cuota/s)",
                'batch' => 0,
                'bonus_type_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Lógica estricta de Fechas
            $oldExpiration = Carbon::parse($user->expiration_date);
            $newExpiration = $oldExpiration->copy()->addMonths($cuotasRequested);
            
            $membershipExpiration = Carbon::parse($user->expiration_membership_date);
            if ($newExpiration->greaterThan($membershipExpiration)) {
                $newExpiration = $membershipExpiration;
            }

            $user->expiration_date = $newExpiration;
            $user->save();

            // Guardar registro en Payments
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $totalAmount;
            $payment->operation_number = "WALLET_" . time(); // Wallet
            $payment->id_payment_method = 3; // Suponiendo que 3 es Wallet. (En Membership usamos 3)
            $payment->details = json_encode([
                'type' => 'opc_repurchase_wallet',
                'cuotas_pagadas' => $cuotasRequested,
                'fecha_anterior' => $oldExpiration->toDateTimeString(),
                'nueva_fecha' => $newExpiration->toDateTimeString()
            ]);
            $payment->save();

            // Distribuir Puntos (Lógica de la antigua aplicación)
            $this->distributePoints($user, $product);

            DB::commit();

            return [
                'success' => true,
                'message' => "Mantenimiento OPC de {$cuotasRequested} cuota(s) aplicado correctamente.",
                'new_expiration' => $newExpiration->format('Y-m-d H:i:s'),
                'new_balance' => $balance - $totalAmount
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar pago OPC con Billetera", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function distributePoints(User $user, Product $product)
    {
        $id = $user->id;
        $fullName = $user->name;
        $membersip = $user->id_account_type;
        $action_user = Classified::where('user_id', $id)->first();

        if (!$action_user) return; // Si no está en el árbol, no hacer nada

        $save_position_branch = $action_user->position;
        $aux = false;

        if ($membersip != 5 && $membersip != 6) {
            $ancestor_id = $action_user->user_above;
            $ancestor_data = $ancestor_id ? Classified::where('user_id', $ancestor_id)->first() : null;
            $aux = (!$ancestor_data || $ancestor_data->user_above == null) ? true : false;

            $iterations = 0;
            while ($aux == false && $iterations < 100) {
                $ancestor_data = Classified::where('user_id', $ancestor_id)->first();
                if (!$ancestor_data) { break; }
                
                $aux = $ancestor_data->user_above == null ? true : false;
                $ancestor_status = User::find($ancestor_id);
                
                if ($ancestor_status && $ancestor_status->active && $ancestor_status->membershipActive) {
                    if ($ancestor_status->qualified || $action_user->id_user_sponsor == $ancestor_data->user_id) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $ancestor_data->user_id,
                            'points' => $product->points,
                            'side' => $save_position_branch,
                            'reason' => "OPC points, " . $fullName
                        ]);
                    }
                }

                $save_position_branch = $ancestor_data->position;
                $ancestor_id = $ancestor_data->user_above;
                $iterations++;
            }
        }
    }
}
