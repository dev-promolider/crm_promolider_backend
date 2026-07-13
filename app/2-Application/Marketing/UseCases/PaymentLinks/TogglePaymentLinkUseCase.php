<?php

namespace Promolider\Application\Marketing\UseCases\PaymentLinks;

use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class TogglePaymentLinkUseCase
{
    public function __construct(
        private readonly PaymentLinkRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?array
    {
        $link = $this->repository->toggleStatus($id);
        return $link->toArray();
    }
}
