<?php
namespace Promolider\Domain\Registration\Entities;

use DateTime;

class Preregistro
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public string $whatsapp,
        public int $referrerId,
        public ?string $accessToken = null,
        public ?DateTime $tokenExpiresAt = null,
        public ?int $status = 0,
        public ?string $referrerUsername = null,
        public ?int $accountType = null,
        public ?string $side = null
    ) {}

    /**
     * Regla de negocio: El token es válido si existe y no ha expirado.
     */
    public function isTokenValid(): bool
    {
        if (!$this->accessToken || !$this->tokenExpiresAt) {
            return false;
        }

        return $this->tokenExpiresAt > new DateTime();
    }

    /**
     * Genera un token de acceso con expiración configurable.
     */
    public function generateToken(int $hours = 72): void
    {
        $this->accessToken = bin2hex(random_bytes(32));
        $this->tokenExpiresAt = (new DateTime())->modify("+{$hours} hours");
    }

    /**
     * Regla de negocio: El preregistro está completado si ya tiene un pago.
     */
    public function isCompleted(): bool
    {
        return $this->status === 2;
    }

    /**
     * Regla de negocio: El preregistro está pendiente de pago.
     */
    public function isPendingPayment(): bool
    {
        return $this->status === 1;
    }
}
