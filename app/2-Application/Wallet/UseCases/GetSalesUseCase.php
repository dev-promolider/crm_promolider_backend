<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;
use App\Models\Option;
use Illuminate\Support\Facades\Log;
use Exception;

class GetSalesUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $authUserId, int $targetUserId)
    {
        Log::info('GetSalesUseCase: Executing getSales', [
            'auth_user_id' => $authUserId,
            'target_user_id' => $targetUserId
        ]);

        $authUser = $this->walletRepository->findUserById($authUserId);
        if (!$authUser) {
            throw new Exception('Authenticated user not found', 404);
        }

        // Authorization check
        $isAuthorised = ($authUserId === $targetUserId) || $authUser->hasRole('Admin');
        if (!$isAuthorised) {
            Log::warning('GetSalesUseCase: Unauthorized access attempt', [
                'auth_user_id' => $authUserId,
                'target_user_id' => $targetUserId
            ]);
            throw new Exception('No tienes permisos para acceder a esta información', 403);
        }

        $wallet = $this->walletRepository->findWalletByUserId($targetUserId);
        if (!$wallet) {
            Log::warning('GetSalesUseCase: Wallet not found', ['user_id' => $targetUserId]);
            return [];
        }

        $lastBatchOpt = Option::lastBatch();
        $lastBatch = $lastBatchOpt ? (int) $lastBatchOpt->value : 1;

        return $this->walletRepository->getSales($wallet->id, $lastBatch);
    }
}
