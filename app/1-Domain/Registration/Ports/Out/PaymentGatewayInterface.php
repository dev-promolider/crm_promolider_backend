<?php
namespace Promolider\Domain\Registration\Ports\Out;

interface PaymentGatewayInterface
{
    /**
     * Crea un cargo en la pasarela de pago (Openpay).
     * Retorna los datos de la redirección 3D Secure.
     */
    public function createCharge(array $chargeData): array;

    /**
     * Guarda un usuario no verificado (pendiente de confirmación de pago).
     */
    public function saveUnverifiedUser(array $userData): void;

    /**
     * Limpia registros previos de usuarios no verificados para el mismo email.
     */
    public function cleanPreviousUnverified(string $email): void;
}
