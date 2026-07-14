<?php

namespace Promolider\Application\Marketing\UseCases\PaymentLinks;

use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class GetPaymentLinkUseCase
{
    public function __construct(
        private PaymentLinkRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?array
    {
        $link = $this->repository->findById($id);
        return $link?->toArray();
    }
}
