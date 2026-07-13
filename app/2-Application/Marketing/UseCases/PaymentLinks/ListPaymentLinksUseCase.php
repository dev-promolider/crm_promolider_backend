<?php

namespace Promolider\Application\Marketing\UseCases\PaymentLinks;

use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class ListPaymentLinksUseCase
{
    public function __construct(
        private readonly PaymentLinkRepositoryInterface $repository,
    ) {}

    public function execute(array $filters = []): array
    {
        return $this->repository->list($filters);
    }
}
