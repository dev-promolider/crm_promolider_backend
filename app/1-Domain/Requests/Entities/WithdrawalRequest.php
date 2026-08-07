<?php

namespace Promolider\Domain\Requests\Entities;

class WithdrawalRequest
{
    private $id;
    private $walletId;
    private $amount;
    private $type;
    private $batch;
    private $status;
    private $reason;
    private $accountType;
    private $accountNumber;
    private $supportImage;
    private $message;
    private $createdAt;
    private $wallet;

    public function __construct(
        $id,
        $walletId,
        $amount,
        $type,
        $batch,
        $status,
        $reason,
        $accountType,
        $accountNumber,
        $supportImage,
        $message,
        $createdAt,
        $wallet = null
    ) {
        $this->id = $id;
        $this->walletId = $walletId;
        $this->amount = $amount;
        $this->type = $type;
        $this->batch = $batch;
        $this->status = $status;
        $this->reason = $reason;
        $this->accountType = $accountType;
        $this->accountNumber = $accountNumber;
        $this->supportImage = $supportImage;
        $this->message = $message;
        $this->createdAt = $createdAt;
        $this->wallet = $wallet;
    }

    public function getId() { return $this->id; }
    public function getWalletId() { return $this->walletId; }
    public function getAmount() { return $this->amount; }
    public function getType() { return $this->type; }
    public function getBatch() { return $this->batch; }
    public function getStatus() { return $this->status; }
    public function getReason() { return $this->reason; }
    public function getAccountType() { return $this->accountType; }
    public function getAccountNumber() { return $this->accountNumber; }
    public function getSupportImage() { return $this->supportImage; }
    public function getMessage() { return $this->message; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getWallet() { return $this->wallet; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->walletId,
            'amount' => $this->amount,
            'type' => $this->type,
            'batch' => $this->batch,
            'status' => $this->status,
            'reason' => $this->reason,
            'account_type' => $this->accountType,
            'account_number' => $this->accountNumber,
            'support_image' => $this->supportImage,
            'message' => $this->message,
            'created_at' => $this->createdAt,
            'wallet' => $this->wallet,
        ];
    }
}
