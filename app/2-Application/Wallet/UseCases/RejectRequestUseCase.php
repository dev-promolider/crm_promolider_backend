<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class RejectRequestUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $requestId): bool
    {
        return $this->walletRepository->rejectRequest($requestId);
    }
}
