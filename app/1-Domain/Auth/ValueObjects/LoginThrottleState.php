<?php
namespace Promolider\Domain\Auth\ValueObjects;

/**
 * Estado de los intentos fallidos de un origen concreto.
 *
 * - failures:    fallos acumulados dentro de la ventana actual.
 * - lockLevel:   cuantas veces se ha agotado el limite historicamente. Es lo que sostiene
 *                el backoff progresivo: sobrevive a la expiracion del bloqueo, por eso el
 *                siguiente castigo es mas largo que el anterior.
 * - lockedUntil: timestamp unix en el que expira el bloqueo, o null si no hay bloqueo.
 *
 * toArray/fromArray definen el contrato de serializacion para que el adaptador de
 * infraestructura sea un simple almacen y no tenga que interpretar nada.
 */
final class LoginThrottleState
{
    public function __construct(
        public int $failures = 0,
        public int $lockLevel = 0,
        public ?int $lockedUntil = null
    ) {}

    public static function empty(): self
    {
        return new self();
    }

    public function isLockedAt(int $now): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil > $now;
    }

    public function secondsUntilUnlock(int $now): int
    {
        if (!$this->isLockedAt($now)) {
            return 0;
        }

        return $this->lockedUntil - $now;
    }

    /**
     * El bloqueo ya caduco pero el historial (lockLevel) sigue contando.
     */
    public function lockHasExpiredAt(int $now): bool
    {
        return $this->lockedUntil !== null && $this->lockedUntil <= $now;
    }

    public function toArray(): array
    {
        return [
            'failures'    => $this->failures,
            'lock_level'  => $this->lockLevel,
            'locked_until' => $this->lockedUntil,
        ];
    }

    public static function fromArray(array $data): self
    {
        $lockedUntil = $data['locked_until'] ?? null;

        return new self(
            (int) ($data['failures'] ?? 0),
            (int) ($data['lock_level'] ?? 0),
            $lockedUntil === null ? null : (int) $lockedUntil
        );
    }
}
