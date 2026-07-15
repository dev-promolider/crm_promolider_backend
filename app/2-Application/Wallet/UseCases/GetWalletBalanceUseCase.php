<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;
use App\Models\WalletMovements;
use Exception;

class GetWalletBalanceUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $userId): array
    {
        $wallet = $this->walletRepository->findWalletByUserId($userId);

        if (!$wallet) {
            throw new Exception('Wallet not found for user', 404);
        }

        // Calculate balance using the same logic as the legacy getWalletBalance
        $totalBalance = WalletMovements::where('wallet_id', $wallet->id)
            ->where('status', 1)
            ->sum('amount');

        $movementsCount = WalletMovements::where('wallet_id', $wallet->id)
            ->where('status', 1)
            ->count();

        $pendingAmount = WalletMovements::where('wallet_id', $wallet->id)
            ->where('status', 0)
            ->sum('amount');

        $approvedAmount = WalletMovements::where('wallet_id', $wallet->id)
            ->where('status', 1)
            ->sum('amount');

        return [
            'user_id' => $userId,
            'wallet_id' => $wallet->id,
            'total_balance' => (float)$totalBalance,
            'formatted_balance' => '$' . number_format($totalBalance, 2),
            'movements_count' => $movementsCount,
            'breakdown' => [
                'approved_amount' => (float)$approvedAmount,
                'pending_amount' => (float)$pendingAmount,
                'formatted_approved' => '$' . number_format($approvedAmount, 2),
                'formatted_pending' => '$' . number_format($pendingAmount, 2)
            ]
        ];
    }
}
