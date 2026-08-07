<?php

namespace Promolider\Infrastructure\Requests\Out\Persistence;

use Promolider\Domain\Requests\Entities\WithdrawalRequest;
use Promolider\Domain\Requests\Repositories\WithdrawalRequestRepositoryInterface;
use App\Models\WalletMovements;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EloquentWithdrawalRequestRepository implements WithdrawalRequestRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15)
    {
        return WalletMovements::with(['wallet.user'])
            ->whereNull('bonus_type_id')
            ->where('status', '!=', 0) // Todos los aprobados o rechazados
            ->where('amount', '<', 0)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getAllPending(): Collection
    {
        $movements = WalletMovements::with(['wallet' => function($query) {
                $query->with(['user']);
            }])
            ->whereNull('bonus_type_id')
            ->where('status', 0)
            ->get();

        // In this case, we might want to just return the Eloquent collection since it's going to be serialized 
        // to JSON, but to adhere to Hexagonal Architecture, we should map them to Entities.
        // However, since relationships like wallet.user are complex to map perfectly without many entities,
        // we'll return an array representation or map to WithdrawalRequest entity and keep relations as stdClass.
        
        return $movements->map(function ($movement) {
            return new WithdrawalRequest(
                $movement->id,
                $movement->wallet_id,
                $movement->amount,
                $movement->type,
                $movement->batch,
                $movement->status,
                $movement->reason,
                $movement->account_type,
                $movement->account_number,
                $movement->support_image,
                $movement->message,
                $movement->created_at,
                $movement->wallet // Keeping Eloquent relation for simplicity in response
            );
        });
    }

    public function findById(int $id): ?WithdrawalRequest
    {
        $movement = WalletMovements::find($id);
        
        if (!$movement) {
            return null;
        }

        return new WithdrawalRequest(
            $movement->id,
            $movement->wallet_id,
            $movement->amount,
            $movement->type,
            $movement->batch,
            $movement->status,
            $movement->reason,
            $movement->account_type,
            $movement->account_number,
            $movement->support_image,
            $movement->message,
            $movement->created_at,
            $movement->wallet
        );
    }

    public function updateStatus(int $id, int $status, ?string $message = null, ?string $supportImage = null): bool
    {
        $movement = WalletMovements::find($id);
        
        if (!$movement) {
            return false;
        }

        $movement->status = $status;
        
        if ($message !== null) {
            $movement->message = $message;
        }

        if ($supportImage !== null) {
            // Delete old image if it exists
            if ($movement->support_image) {
                $existingPath = str_replace(env('APP_URL') . '/storage/', '', $movement->support_image);
                Storage::disk('s3')->delete($existingPath);
                Log::info('WalletMovements: Imagen anterior eliminada', ['deleted_path' => $existingPath]);
            }

            $movement->support_image = $supportImage;
        }

        return $movement->save();
    }
}
