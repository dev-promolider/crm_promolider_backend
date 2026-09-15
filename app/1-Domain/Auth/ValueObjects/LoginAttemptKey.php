<?php
namespace Promolider\Domain\Auth\ValueObjects;

/**
 * Identifica de forma estable un origen de intentos de login.
 *
 * El endpoint acepta indistintamente username o email (ver EloquentUserRepository),
 * asi que la clave se construye sobre el identificador tal y como lo envia el cliente,
 * normalizado, y nunca sobre el usuario resuelto en base de datos: el limite debe poder
 * aplicarse ANTES de consultar la BD, que es justo lo que se quiere proteger.
 *
 * El valor se hashea para no dejar credenciales parciales en texto plano dentro del cache.
 */
final class LoginAttemptKey
{
    private function __construct(
        private string $value
    ) {}

    public static function fromIdentifierAndIp(string $identifier, string $ip): self
    {
        $normalizedIdentifier = mb_strtolower(trim($identifier));
        $normalizedIp         = trim($ip);

        return new self(hash('sha256', $normalizedIdentifier . '|' . $normalizedIp));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
