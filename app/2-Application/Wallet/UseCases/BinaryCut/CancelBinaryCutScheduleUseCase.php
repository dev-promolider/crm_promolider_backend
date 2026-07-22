<?php

namespace Promolider\Application\Wallet\UseCases\BinaryCut;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class CancelBinaryCutScheduleUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(): void
    {
        $this->walletRepository->cancelBinaryCutSchedule();
    }
}
