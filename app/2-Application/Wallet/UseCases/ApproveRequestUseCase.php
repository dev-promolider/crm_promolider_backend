<?php

namespace Promolider\Application\Wallet\UseCases;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class ApproveRequestUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    public function execute(int $requestId, ?string $message, $imageFile): bool
    {
        return $this->walletRepository->approveRequest($requestId, $message, $imageFile);
    }
}
