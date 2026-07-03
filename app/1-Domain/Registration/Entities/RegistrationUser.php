<?php
namespace Promolider\Domain\Registration\Entities;

class RegistrationUser
{
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
     * Regla de negocio: Las cuentas de pago tienen membresía de 365 días.
     */
    public function getMembershipExpirationTimestamp(): ?int
    {
        return $this->isFreeTier()
            ? strtotime('+365 days')
            : null;
    }
}
