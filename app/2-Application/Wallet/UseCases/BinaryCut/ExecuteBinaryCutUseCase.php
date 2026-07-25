<?php

namespace Promolider\Application\Wallet\UseCases\BinaryCut;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class ExecuteBinaryCutUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(): void
    {
        $this->walletRepository->executeBinaryCut();
    }
}
