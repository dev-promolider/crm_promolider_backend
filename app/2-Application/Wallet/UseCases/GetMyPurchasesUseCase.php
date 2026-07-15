<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class GetMyPurchasesUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $userId)
    {
        return $this->walletRepository->getMyPurchases($userId);
    }
}
