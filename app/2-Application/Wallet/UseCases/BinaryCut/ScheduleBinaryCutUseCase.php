<?php

namespace Promolider\Application\Wallet\UseCases\BinaryCut;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class ScheduleBinaryCutUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(string $datetime): void
    {
        $this->walletRepository->setBinaryCutSchedule($datetime);
    }
}
