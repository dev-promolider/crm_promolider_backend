<?php

namespace Promolider\Application\Marketing\UseCases\PaymentLinks;

use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class UpdatePaymentLinkUseCase
{
    public function __construct(
        private readonly PaymentLinkRepositoryInterface $repository,
    ) {}

    public function execute(int $id, array $data): ?array
    {
        $link = $this->repository->update($id, $data);
        return $link->toArray();
    }
}
