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
     * Tipos de cuenta gratuitos: 6 (Productor Invitado) y 7 (Consumidor Invitado).
     * No requieren pago y se aprueban inmediatamente (request = 2).
     *
     * Antes esta lista era [5, 9], que estaba mal por los dos lados: el 5 es
     * "Socio Fundador" y cuesta 1313.56, y el 9 no existe. La consecuencia era doble:
     * el productor gratuito quedaba pendiente de aprobacion y no contaba para nada en
     * la red, y un alta por API con tipo 5 se daba por gratuita, aprobada y sin pago.
     */
    public const TIPOS_GRATUITOS = [6, 7];

    /** Cuenta con la que entra quien se registra desde el marketplace. */
    public const CUENTA_CONSUMIDOR = 7;

    public function isFreeTier(): bool
    {
        return in_array($this->idAccountType, self::TIPOS_GRATUITOS, true);
    }

    /**
     * El consumidor llega comprando un curso del marketplace, no por un enlace de
     * patrocinio, asi que su patrocinador no elige pierna: se le coloca en la mas
     * debil para que la estructura se compense sola.
     */
    public function isConsumer(): bool
    {
        return $this->idAccountType === self::CUENTA_CONSUMIDOR;
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
