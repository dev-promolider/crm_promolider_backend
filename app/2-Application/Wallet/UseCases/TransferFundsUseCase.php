<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;
use App\Models\Option;
use Illuminate\Support\Facades\Log;
use Exception;

class TransferFundsUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $senderId, int $receiverId, float $amount): array
    {
        Log::info('TransferFundsUseCase: Executing transfer', [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'amount' => $amount
        ]);

        // 1. Verify receiver is a direct referral of the sender
        $directs = $this->walletRepository->findDirectReferrals($senderId);
        if (!in_array($receiverId, $directs)) {
            Log::error('TransferFundsUseCase: Receiver is not a direct referral', [
                'sender_id' => $senderId,
                'attempted_receiver_id' => $receiverId
            ]);
            throw new Exception('Solo puedes transferir fondos a tus referidos directos', 403);
        }

        // 2. Fetch wallets
        $senderWallet = $this->walletRepository->findWalletByUserId($senderId);
        if (!$senderWallet) {
            throw new Exception('Billetera del remitente no encontrada', 404);
        }

        $receiverWallet = $this->walletRepository->findWalletByUserId($receiverId);
        if (!$receiverWallet) {
            throw new Exception('Billetera del receptor no encontrada', 404);
        }

        // 3. Verify sender balance
        $balance = $this->walletRepository->retrieveWalletBalanceUser($senderId);
        if ($balance < $amount) {
            Log::warning('TransferFundsUseCase: Insufficient funds', [
                'sender_id' => $senderId,
                'balance' => $balance,
                'amount_requested' => $amount
            ]);
            throw new Exception('Fondos insuficientes en la billetera', 400);
        }

        // 4. Fetch target user to get username
        $senderUser = $this->walletRepository->findUserById($senderId);
        $receiverUser = $this->walletRepository->findUserById($receiverId);
        if (!$senderUser || !$receiverUser) {
            throw new Exception('Usuario no encontrado', 404);
        }

        // 5. Get batch
        $lastBatch = Option::lastBatch();
        $batch = $lastBatch ? (int) $lastBatch->value : 1;

        // 6. Perform repository transfer
        $result = $this->walletRepository->transferFunds(
            $senderWallet->id,
            $receiverWallet->id,
            $amount,
            $senderId,
            $receiverId,
            $senderUser->username,
            $receiverUser->username,
            $batch
        );

        // 7. Get new balance
        $newBalance = $this->walletRepository->retrieveWalletBalanceUser($senderId);

        return [
            'status' => 'ok',
            'amount_transferred' => '$' . number_format($amount, 2),
            'new_balance' => '$' . number_format($newBalance, 2),
            'receiver' => $receiverUser->username,
            'batch' => $batch
        ];
    }
}
