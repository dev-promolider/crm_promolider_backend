<?php

namespace Promolider\Application\Requests\UseCases\Withdrawal;

use Promolider\Domain\Requests\Repositories\WithdrawalRequestRepositoryInterface;
use Illuminate\Support\Collection;

class ListWithdrawalRequestsUseCase
{
    private $repository;

    public function __construct(WithdrawalRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): Collection
    {
        return $this->repository->getAllPending();
    }
}
