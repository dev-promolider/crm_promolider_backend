<?php

namespace Promolider\Application\Marketing\UseCases\InvitationLinks;

use Promolider\Domain\Marketing\Ports\Out\ProductDistributorRepositoryInterface;
use Carbon\Carbon;

class CreateInvitationLinkUseCase
{
    public function __construct(
        private readonly ProductDistributorRepositoryInterface $repository,
    ) {}

    public function execute(string $productType, int $productId, int $userId): array
    {
        // Check if invitation already exists
        $existing = $this->repository->findExistingInvitation($productType, $productId, $userId);
        if ($existing && $existing->exists()) {
            return $existing->toArray();
        }

        // Generate random code
        $code = substr(bin2hex(random_bytes(5)), 0, 10);
        $expiresAt = Carbon::now()->addDays(7);

        $distributor = $this->repository->createInvitation(
            $productType,
            $productId,
            $userId,
            $code,
            $expiresAt->toIso8601String()
        );

        return $distributor->toArray();
    }
}
