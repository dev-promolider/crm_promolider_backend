<?php
namespace Promolider\Domain\Registration\Entities;

class UnverifiedPayment
{
    public function __construct(
        public ?int $id = null,
        public string $email = '',
        public ?string $openpayOrderId = null,
        public float $productPrice = 0.0,
        public array $userData = [],
        public ?string $redirectUrl = null,
        public ?string $createdAt = null
    ) {}

    /**
     * Regla de negocio: El pago no verificado tiene un ID de Openpay válido.
     */
    public function hasValidOrderId(): bool
    {
        return !empty($this->openpayOrderId);
    }
}
