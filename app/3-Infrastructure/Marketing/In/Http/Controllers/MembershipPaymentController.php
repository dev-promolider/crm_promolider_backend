<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AccountType;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipPaymentController extends Controller
{
    public function purchaseWithWallet(Request $request)
    {
        $request->validate([
            "plan_id" => "required|integer",
        ]);

        $user = $request->user();
        $plan_id = $request->plan_id;

        $newPlan = AccountType::findOrFail($plan_id);

        $isExpired = false;
        if ($user->expiration_membership_date) {
            $isExpired = Carbon::now()->greaterThan(Carbon::parse($user->expiration_membership_date));
        } else {
            $isExpired = true;
        }

        // Calculate amount
        $amount = 0;
        if ($isExpired || !$user->id_account_type) {
            // Full price + IVA
            $amount = $newPlan->price + ($newPlan->price * ($newPlan->iva / 100));
        } else {
            // Difference
            $activePlan = AccountType::find($user->id_account_type);
            $activePrice = $activePlan ? $activePlan->price : 0;
            $amount = $newPlan->price - $activePrice;
            // Frontend doesnt calculate IVA on upgrade, so we dont either
            if ($amount < 0) $amount = 0;
        }

        try {
            DB::beginTransaction();

            $wallet = Wallet::where("user_id", $user->id)->first();
            if (!$wallet) {
                // If user doesn't have a wallet, create one
                $wallet = new Wallet();
                $wallet->user_id = $user->id;
                $wallet->active = 1;
                $wallet->save();
            }

            // check real balance
            $movements = WalletMovements::where("wallet_id", $wallet->id)->get();
            $balance = 0;
            foreach ($movements as $m) {
                $balance += $m->amount;
            }

            // A small threshold for floating point inaccuracies
            if ($balance < ($amount - 0.01)) {
                throw new \Exception("Saldo insuficiente en la billetera.");
            }

            // Create debit movement
            $mov = new WalletMovements();
            $mov->wallet_id = $wallet->id;
            $mov->amount = -$amount;
            $mov->type = 0;
            $mov->batch = 0;
            $mov->bonus_type_id = null;
            $mov->reason = "Mejora/Pago de membresía " . $newPlan->account;
            $mov->save();

            // Create membership history
            $purchaseDate = now();
            $expirationDate = (clone $purchaseDate)->addDays(365); // Default 1 year

            $detailId = DB::table("account_type_details")->insertGetId([
                "user_id" => $user->id,
                "purchase_date" => $purchaseDate,
                "expiration_date" => $expirationDate,
                "status" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]);

            DB::table("account_type_detail_histories")->insert([
                "account_type_detail_id" => $detailId,
                "account_type_id" => $newPlan->id,
                "purchase_date" => $purchaseDate,
                "expiration_date" => $expirationDate,
                "status" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]);

            // Update user
            $user->id_account_type = $newPlan->id;
            $user->expiration_membership_date = $expirationDate;
            $user->expiration_date = (clone $purchaseDate)->addDays(30); // 1 mes de OPC
            $user->save();

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Membresía actualizada con éxito.",
                "user" => $user,
                "new_balance" => $balance - $amount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("MembershipPurchase Error: " . $e->getMessage() . " - Line: " . $e->getLine());
            return response()->json(["error" => $e->getMessage()], 400);
        }
    }
}
