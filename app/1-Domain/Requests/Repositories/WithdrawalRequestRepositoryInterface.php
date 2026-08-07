<?php

namespace Promolider\Domain\Requests\Repositories;

use Promolider\Domain\Requests\Entities\WithdrawalRequest;
use Illuminate\Support\Collection;

interface WithdrawalRequestRepositoryInterface
{
    /**
     * Get all pending withdrawal requests (status = 0)
     *
     * @return Collection
     */
    public function getAllPending(): Collection;

    /**
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15);

    /**
     * Get a specific withdrawal request by ID
     *
     * @param int $id
     * @return WithdrawalRequest|null
     */
    public function findById(int $id): ?WithdrawalRequest;

    /**
     * Update the status and optionally message and support image of a request
     *
     * @param int $id
     * @param int $status (1 for approved, 2 for rejected)
     * @param string|null $message
     * @param string|null $supportImage
     * @return bool
     */
    public function updateStatus(int $id, int $status, ?string $message = null, ?string $supportImage = null): bool;
}
