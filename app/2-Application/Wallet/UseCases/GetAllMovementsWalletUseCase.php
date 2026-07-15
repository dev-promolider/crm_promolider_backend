<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class GetAllMovementsWalletUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $authUserId, int $targetUserId, ?string $dateFrom, ?string $dateTo, ?string $status, ?string $search, int $perPage, int $page)
    {
        Log::info('GetAllMovementsWalletUseCase: Executing', [
            'auth_user_id'   => $authUserId,
            'target_user_id' => $targetUserId
        ]);

        if ($authUserId !== $targetUserId) {
            Log::warning('GetAllMovementsWalletUseCase: Unauthorized access attempt', [
                'auth_user_id'   => $authUserId,
                'target_user_id' => $targetUserId
            ]);
            throw new Exception('No tienes permisos para ver los movimientos de este usuario', 403);
        }

        $wallet = $this->walletRepository->findWalletByUserId($targetUserId);
        if (!$wallet) {
            Log::warning('GetAllMovementsWalletUseCase: Wallet not found', [
                'user_id' => $targetUserId
            ]);
            throw new Exception('Wallet not found', 404);
        }

        return $this->walletRepository->getAllMovementsWallet(
            $wallet->id,
            $targetUserId,
            $dateFrom,
            $dateTo,
            $status,
            $search,
            $perPage,
            $page
        );
    }
}
