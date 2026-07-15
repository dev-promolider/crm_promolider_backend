<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class GetAllMovementsHistoryWalletUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute()
    {
        return $this->walletRepository->getAllMovementsHistory();
    }
}
