<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class GetBinaryHistoryUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $userId, ?string $search, string $sortKey, string $sortOrder, int $perPage)
    {
        return $this->walletRepository->getBinaryHistory($userId, $search, $sortKey, $sortOrder, $perPage);
    }
}
