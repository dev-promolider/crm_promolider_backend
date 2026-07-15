<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;
use App\Models\User;
use App\Models\Option;
use Illuminate\Support\Facades\Log;
use Exception;

class RequestFundsUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $userId, float $amount, string $accountType, string $accountNumber): array
    {
        Log::info('RequestFundsUseCase: Executing request', [
            'user_id' => $userId,
            'amount' => $amount,
            'account_type' => $accountType
        ]);

        // Find Admin user
        $admin = User::where('username', 'admin')->first();
        if (!$admin) {
            Log::error('RequestFundsUseCase: Admin user not found');
            throw new Exception('Admin user not found', 500);
        }

        // Find wallet
        $wallet = $this->walletRepository->findWalletByUserId($userId);
        if (!$wallet) {
            Log::error('RequestFundsUseCase: Wallet not found', ['user_id' => $userId]);
            throw new Exception('Wallet not found', 404);
        }

        // Get batch
        $lastBatch = Option::lastBatch();
        $batch = $lastBatch ? (int) $lastBatch->value : 1;

        $result = $this->walletRepository->requestFunds(
            $wallet->id,
            $amount,
            $accountType,
            $accountNumber,
            $userId,
            $admin->id,
            $batch
        );

        return [
            'status' => 'ok',
            'message' => 'Operación exitosa',
            'data' => $result
        ];
    }
}
