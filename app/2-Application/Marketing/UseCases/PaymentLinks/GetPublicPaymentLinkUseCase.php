<?php

namespace Promolider\Application\Marketing\UseCases\PaymentLinks;

use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class GetPublicPaymentLinkUseCase
{
    public function __construct(
        private readonly PaymentLinkRepositoryInterface $repository,
    ) {}

    public function execute(string $slug): ?array
    {
        $link = $this->repository->findBySlug($slug);
        if (!$link || !$link->isActive()) {
            return null;
        }

        // Increment usage count
        if ($link->getId()) {
            $this->repository->incrementUsage($link->getId());
        }

        return $link->toArray();
    }
}
