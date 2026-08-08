<?php
namespace Promolider\Domain\Registration\Entities;

class RegistrationUser
{
    private bool $forceVerified = false;

    public function __construct(
        public ?int $id = null,
        public string $username = '',
        public string $email = '',
        public string $name = '',
        public string $lastName = '',
        public string $phone = '',
        public ?string $dateBirth = null,
        public int $idCountry = 0,
        public int $idDocumentType = 0,
        public string $nroDocument = '',
        public int $idAccountType = 0,
        public int $idReferrerSponsor = 0,
        public string $password = '',
        public string $biography = '',
        public ?int $position = null,
        public ?string $city = 'ciudad',
        public ?string $photo = null,
        public ?string $userType = null
    ) {}

    public function setAsVerified(): self
    {
        $this->forceVerified = true;
        return $this;
    }

    /**
     * Regla de negocio: Las cuentas tipo 5 (free) y 9 (free) son gratuitas.
     * No requieren pago y se aprueban inmediatamente (request = 2).
     */
    public function isFreeTier(): bool
    {
        return in_array($this->idAccountType, [5, 9]);
    }

    /**
     * Regla de negocio: El valor de 'request' determina el estado de la solicitud.
     * 1 = pendiente, 2 = aprobado, 3 = rechazado.
     * Las cuentas free se aprueban directamente.
     */
    public function getRequestStatus(): int
    {
        if ($this->forceVerified) return 2;
        return $this->isFreeTier() ? 2 : 1;
    }

    /**
     * Regla de negocio: Las cuentas free expiran en 10 años.
     * Las cuentas de pago expiran en 30 días.
     */
    public function getExpirationTimestamp(): int
    {
        return $this->isFreeTier()
            ? strtotime('+10 years')
            : strtotime('+30 days');
    }

    /**
     * Regla de negocio: Todas las cuentas tienen membresía de 365 días al momento del pago o activación gratuita.
     */
    public function getMembershipExpirationTimestamp(): ?int
    {
        return strtotime('+365 days');
    }
}
