<?php

namespace Promolider\Application\Requests\UseCases\Withdrawal;

use Promolider\Domain\Requests\Repositories\WithdrawalRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListApprovedWithdrawalRequestsUseCase
{
    private WithdrawalRequestRepositoryInterface $repository;

    public function __construct(WithdrawalRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }
}
