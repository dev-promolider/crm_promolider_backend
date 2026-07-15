<?php

namespace Promolider\Infrastructure\Wallet\Out\Persistence;

use App\Models\Wallet;
use App\Models\WalletMovements;
use App\Models\User;
use App\Models\BinaryCutHistory;
use App\Models\Option;
use App\Models\AccountType;
use App\Models\Payment;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class EloquentWalletRepository implements WalletRepositoryInterface
{
    public function findWalletByUserId(int $userId)
    {
        return Wallet::where('user_id', $userId)->first();
    }

    public function findUserById(int $userId)
    {
        return User::find($userId);
    }

    public function findDirectReferrals(int $userId): array
    {
        return User::where('id_referrer_sponsor', $userId)->pluck('id')->toArray();
    }

    public function retrieveWalletBalanceUser(int $userId): float
    {
        $user = User::find($userId);
        if (!$user) {
            return 0.0;
        }

        $accountType = AccountType::find($user->id_account_type);
        $isAdmin = $accountType && $accountType->account === 'Admin';

        if ($isAdmin) {
            return 0.0;
        }

        $myWallet = Wallet::where('user_id', $userId)->first();
        if (!$myWallet) {
            return 0.0;
        }

        // Get movements matching same logic as original
        $myMovements = WalletMovements::where('wallet_id', $myWallet->id)
            ->orWhere('id_receiver', $userId)
            ->get();

        $result = array_reduce($myMovements->toArray(), function ($carry, $item) use ($userId) {
            if ($item['type'] == 1) {
                return $carry + (float) $item['amount'];
            } else if ($item['type'] == 0) {
                if ($item['id_receiver'] === $userId) {
                    return $carry + (float) $item['amount'];
                } else {
                    return $carry - (float) $item['amount'];
                }
            }
            return $carry;
        }, 0.0);

        return (float) $result;
    }

    public function getAllMovementsWallet(int $walletId, int $userId, ?string $dateFrom, ?string $dateTo, ?string $status, ?string $search, int $perPage, int $page)
    {
        $statusMap = ['approved' => 1, 'pending' => 0, 'rejected' => 2];

        $query = WalletMovements::where(function ($q) use ($walletId, $userId) {
            $q->where('wallet_id', $walletId)
              ->orWhere('id_receiver', $userId);
        });

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($status && array_key_exists($status, $statusMap)) {
            $query->where('status', $statusMap[$status]);
        }
        if ($search) {
            $query->where('reason', 'like', '%' . $search . '%');
        }

        return $query->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAllMovementsHistory()
    {
        return WalletMovements::where('status', 1)
            ->select('created_at', 'amount', 'reason', 'type')
            ->get();
    }

    public function transferFunds(int $senderWalletId, int $receiverWalletId, float $amount, int $senderId, int $receiverId, string $senderUsername, string $receiverUsername, int $batch): array
    {
        DB::beginTransaction();
        try {
            // Debit movement
            $debitMovement = new WalletMovements();
            $debitMovement->wallet_id = $senderWalletId;
            $debitMovement->amount = -$amount;
            $debitMovement->type = 0;
            $debitMovement->batch = $batch;
            $debitMovement->id_receiver = $receiverId;
            $debitMovement->reason = 'Transfer of funds from ' . $senderUsername . ' to ' . $receiverUsername;
            $debitMovement->status = 1; // Approved automatically for direct transfers
            $debitMovement->save();

            // Credit movement
            $creditMovement = new WalletMovements();
            $creditMovement->wallet_id = $receiverWalletId;
            $creditMovement->amount = $amount;
            $creditMovement->type = 1;
            $creditMovement->batch = $batch;
            $creditMovement->id_receiver = $senderId;
            $creditMovement->reason = 'Transfer of funds from ' . $senderUsername . ' to ' . $receiverUsername;
            $creditMovement->status = 1; // Approved automatically for direct transfers
            $creditMovement->save();

            DB::commit();

            return [
                'success' => true,
                'debit_movement' => $debitMovement,
                'credit_movement' => $creditMovement
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('EloquentWalletRepository: Error during transferFunds: ' . $e->getMessage());
            throw $e;
        }
    }

    public function requestFunds(int $walletId, float $amount, string $accountType, string $accountNumber, int $userId, int $adminId, int $batch): array
    {
        DB::beginTransaction();
        try {
            $movement = new WalletMovements();
            $movement->wallet_id = $walletId;
            $movement->amount = $amount;
            $movement->type = 0;
            $movement->batch = $batch;
            $movement->status = 0; // Pending
            $movement->reason = 'Solicitud de fondos';
            $movement->account_type = $accountType;
            $movement->account_number = $accountNumber;
            $movement->save();

            $user = User::find($userId);
            $notification = new \App\Models\Notifications();
            $notification->id_generator = $userId;
            $notification->id_receiver = $adminId;
            $notification->title = "Solicitud de Fondos";
            $notification->body = ($user->name ?? 'Usuario') . " solicita el retiro de $ " . $amount;
            $notification->type = 1;
            $notification->save();

            DB::commit();

            return [
                'success' => true,
                'movement' => $movement,
                'notification' => $notification
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('EloquentWalletRepository: Error during requestFunds: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRequestFundsList()
    {
        return WalletMovements::with(['wallet' => function($query){
            $query->with(['user']);
        }])->where('bonus_type_id', null)
            ->where('status', 0)->get();
    }

    public function rejectRequest(int $requestId): bool
    {
        $wallet_movement = WalletMovements::findOrFail($requestId);
        $wallet_movement->status = 2; // Rejected
        return $wallet_movement->update();
    }

    public function approveRequest(int $requestId, ?string $message, $imageFile): bool
    {
        $wallet_movement = WalletMovements::findOrFail($requestId);

        if ($imageFile) {
            $formattedFilename = Helper::formatFilename($imageFile->getClientOriginalName());
            $path = 'support_images/' . $formattedFilename;

            if ($wallet_movement->support_image) {
                $existingPath = str_replace(env('APP_URL') . '/storage/', '', $wallet_movement->support_image);
                Storage::disk('s3')->delete($existingPath);
            }

            $options = [
                'visibility' => 'public',
                'ContentDisposition' => 'attachment; filename="' . $formattedFilename . '"',
            ];

            Storage::disk('s3')->put($path, file_get_contents($imageFile), $options);
            $wallet_movement->support_image = Storage::disk('s3')->url($path);
        }

        $wallet_movement->message = $message;
        $wallet_movement->status = 1; // Approved
        return $wallet_movement->update();
    }

    public function getBinaryHistory(int $userId, ?string $search, string $sortKey, string $sortOrder, int $perPage)
    {
        $query = BinaryCutHistory::where('user_id', $userId)
            ->with(['rank']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('rank', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%");
                })->orWhere('created_at', 'like', "%{$search}%");
            });
        }

        $sortMap = [
            'rank.name' => 'rank_id',
            'created_at' => 'created_at'
        ];

        $query->orderBy(
            $sortMap[$sortKey] ?? $sortKey,
            $sortOrder
        );

        return $query->paginate($perPage);
    }

    public function getSales(int $walletId, int $batch)
    {
        return DB::table('wallet_movements as wallet')
            ->join('users as us', 'wallet.user_purchase_id', '=', 'us.id')
            ->select('us.name', 'us.last_name', 'wallet.amount', 'wallet.reason', 'wallet.created_at', 'wallet.bonus_type_id')
            ->where('wallet.wallet_id', $walletId)
            ->whereIn('wallet.bonus_type_id', [2, 3])
            ->where('wallet.batch', $batch)
            ->get();
    }

    public function getMyDirects(int $userId)
    {
        return User::where('id_referrer_sponsor', $userId)->get();
    }

    public function getMyPurchases(int $userId)
    {
        return Payment::query()
            ->where('user_id', $userId)
            ->with(['paymentMethod', 'user'])
            ->get();
    }
}
