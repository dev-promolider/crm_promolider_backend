<?php

namespace Promolider\Application\Marketing\UseCases\InvitationLinks;

use Promolider\Domain\Marketing\Ports\Out\ProductDistributorRepositoryInterface;

class CheckInvitationUseCase
{
    public function __construct(
        private readonly ProductDistributorRepositoryInterface $repository,
    ) {}

    public function execute(string $productType, int $productId, int $userId): array
    {
        $existing = $this->repository->findExistingInvitation($productType, $productId, $userId);

        return [
            'exists' => $existing !== null && $existing->exists(),
            'invitation_link' => $existing?->getInvitationLink(),
            'data' => $existing?->toArray(),
        ];
    }
}
