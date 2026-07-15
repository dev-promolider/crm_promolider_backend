<?php

namespace Promolider\Domain\Wallet\Ports\Out;

interface WalletRepositoryInterface
{
    public function findWalletByUserId(int $userId);
    public function findUserById(int $userId);
    public function findDirectReferrals(int $userId): array;
    public function retrieveWalletBalanceUser(int $userId): float;
    public function getAllMovementsWallet(int $walletId, int $userId, ?string $dateFrom, ?string $dateTo, ?string $status, ?string $search, int $perPage, int $page);
    public function getAllMovementsHistory();
    public function transferFunds(int $senderWalletId, int $receiverWalletId, float $amount, int $senderId, int $receiverId, string $senderUsername, string $receiverUsername, int $batch): array;
    public function requestFunds(int $walletId, float $amount, string $accountType, string $accountNumber, int $userId, int $adminId, int $batch): array;
    public function getRequestFundsList();
    public function rejectRequest(int $requestId): bool;
    public function approveRequest(int $requestId, ?string $message, $imageFile): bool;
    public function getBinaryHistory(int $userId, ?string $search, string $sortKey, string $sortOrder, int $perPage);
    public function getSales(int $walletId, int $batch);
    public function getMyDirects(int $userId);
    public function getMyPurchases(int $userId);
}
