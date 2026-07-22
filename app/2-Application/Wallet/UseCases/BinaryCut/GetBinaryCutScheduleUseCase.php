<?php

namespace Promolider\Application\Wallet\UseCases\BinaryCut;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class GetBinaryCutScheduleUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(): ?string
    {
        return $this->walletRepository->getBinaryCutSchedule();
    }
}
