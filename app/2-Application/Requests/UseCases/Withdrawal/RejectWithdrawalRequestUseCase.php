<?php

namespace Promolider\Application\Requests\UseCases\Withdrawal;

use Promolider\Domain\Requests\Repositories\WithdrawalRequestRepositoryInterface;
use Exception;

class RejectWithdrawalRequestUseCase
{
    private $repository;

    public function __construct(WithdrawalRequestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        $request = $this->repository->findById($id);
        
        if (!$request) {
            throw new Exception("Withdrawal request not found.");
        }

        if ($request->getStatus() !== 0) {
            throw new Exception("Request is not pending.");
        }

        return $this->repository->updateStatus($id, 2);
    }
}
