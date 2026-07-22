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

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('s3');
            $disk->put($path, file_get_contents($imageFile), $options);
            $wallet_movement->support_image = $disk->url($path);
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

    public function getMyPurchases(int $userId, ?string $search, int $perPage, int $page)
    {
        $query = Payment::query()
            ->where('user_id', $userId)
            ->with(['paymentMethod', 'user']);
            
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('operation_number', 'like', '%' . $search . '%')
                  ->orWhere('details', 'like', '%' . $search . '%')
                  ->orWhereHas('paymentMethod', function($qMethod) use ($search) {
                      $qMethod->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        $stats = [
            'total_invested' => Payment::where('user_id', $userId)->sum('amount'),
            'total_transactions' => Payment::where('user_id', $userId)->count(),
            'last_purchase_date' => Payment::where('user_id', $userId)->latest()->value('created_at')
        ];

        return [
            'stats' => $stats,
            'paginator' => $query->latest()->paginate($perPage, ['*'], 'page', $page)
        ];
    }

    public function getBinaryCutSchedule(): ?string
    {
        $option = Option::where('description', 'binary_cut_scheduled_at')->first();
        return $option ? $option->value : null;
    }

    public function setBinaryCutSchedule(string $datetime): void
    {
        Option::updateOrCreate(
            ['description' => 'binary_cut_scheduled_at'],
            ['value' => $datetime]
        );
    }

    public function cancelBinaryCutSchedule(): void
    {
        Option::where('description', 'binary_cut_scheduled_at')->delete();
    }

    public function executeBinaryCut(): void
    {
        DB::beginTransaction();
        try {
            $users = User::all();
            
            $ranks = \App\Models\RankBonus::select('id', 'vol_min', 'pack_max', 'active_direct', 'max_pay', 'monthly_bonus', 'limit_generation')->get();
            $batchOption = Option::firstOrCreate(['description' => 'batch'], ['value' => '1']);
            $lastBatch = (int) $batchOption->value;
            
            $calculator = new \App\Services\MLM\BinaryCutCalculatorService();
            $userPointsCache = $calculator->calculateBinaryPointsLocally($users);
            
            foreach ($users as $user) {
                $userLeftPoints = $userPointsCache[$user->id]['left'] ?? 0;
                $userRightPoints = $userPointsCache[$user->id]['right'] ?? 0;
                
                if ($userLeftPoints == 0 && $userRightPoints == 0) continue;
                
                $maxPoints = max($userLeftPoints, $userRightPoints);
                $minPoints = min($userLeftPoints, $userRightPoints);
                $sideMax = $userLeftPoints > $userRightPoints ? 0 : 1;
                
                $myRank = $this->setRanks($user->id, $minPoints, $ranks, $lastBatch);
                
                $myWallet = Wallet::where('user_id', $user->id)->first();
                if (!$myWallet) continue;
                
                $maxTransfer = $myRank->max_pay;
                
                // percentage based on account type
                $accountType = AccountType::find($user->id_account_type);
                $payInBinary = $accountType ? (float) $accountType->pay_in_binary : 0;
                
                $amountToTransfer = ($minPoints * 1) * ($payInBinary / 100);
                
                if ($amountToTransfer > $maxTransfer) {
                    $amountToTransfer = $maxTransfer;
                }
                
                // Update active points status to inactive using index
                \App\Models\Point::where('user_id', $user->id)->where('status', 1)->update(['status' => 0]);
                
                // Create remnant points
                \App\Models\Point::create([
                    'user_id' => $user->id,
                    'points' => $maxPoints - $minPoints,
                    'side' => $sideMax,
                    'reason' => "Binary cut"
                ]);
                
                if ($amountToTransfer > 0) {
                    $movement = new WalletMovements();
                    $movement->wallet_id = $myWallet->id;
                    $movement->amount = $amountToTransfer;
                    $movement->type = 1;
                    $movement->reason = 'Bono binario';
                    $movement->batch = $lastBatch;
                    $movement->bonus_type_id = 4;
                    $movement->save();
                }
                
                BinaryCutHistory::create([
                    'user_id' => $user->id,
                    'rank_id' => $myRank->id,
                    'left_points' => $userLeftPoints,
                    'right_points' => $userRightPoints,
                    'transferred_amount' => $amountToTransfer,
                    'batch' => $lastBatch
                ]);
            }
            
            // Advance batch
            $batchOption->value = (string)($lastBatch + 1);
            $batchOption->save();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error executing binary cut: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function setRanks($user_id, $min_points, $ranks, $batch)
    {
        $my_rank = $ranks->filter(function ($value) use ($min_points) {
            return $min_points >= $value->vol_min;
        })->last();
        
        if (!$my_rank) {
            $my_rank = $ranks->first();
        }
        
        DB::table('rank_binary')->insert([
            'user_id' => $user_id,
            'rank_id' => $my_rank->id,
            'batch' => $batch,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return $my_rank;
    }
}
