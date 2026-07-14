<?php

namespace Promolider\Application\Marketing\UseCases\PaymentLinks;

use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class CreatePaymentLinkUseCase
{
    public function __construct(
        private readonly PaymentLinkRepositoryInterface $repository,
    ) {}

    public function execute(array $data): array
    {
        $link = $this->repository->create($data);
        return $link->toArray();
    }
}
