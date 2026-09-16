<?php
namespace Promolider\Domain\Auth\Services;

use Promolider\Domain\Auth\Exceptions\TooManyLoginAttemptsException;
use Promolider\Domain\Auth\Ports\Out\LoginThrottleStoreInterface;
use Promolider\Domain\Auth\ValueObjects\LoginAttemptKey;
use Promolider\Domain\Auth\ValueObjects\LoginThrottleState;

/**
 * Servicio de dominio: politica de intentos fallidos de login.
 *
 * Reglas:
 *  - Hasta $maxAttempts fallos por clave (identificador + IP). El quinto bloquea.
 *  - Cada bloqueo dura el doble que el anterior ($baseLockSeconds * multiplicador^(nivel-1)),
 *    con techo en $maxLockSeconds para no crecer sin control.
 *  - Al expirar un bloqueo el contador de fallos vuelve a cero (un usuario legitimo que se
 *    equivoco recupera su margen completo), pero el NIVEL de bloqueo se conserva: si el mismo
 *    origen vuelve a agotar los intentos, el castigo siguiente es el doble de largo.
 *  - Un login correcto borra todo el estado, incluido el nivel.
 *
 */
class LoginThrottle
{
    /**
     * Techo del nivel de backoff. Evita desbordar la potencia y mantener numeros absurdos
     * en el almacen una vez que el bloqueo ya toca el maximo.
     */
    private const MAX_LOCK_LEVEL = 16;

    public function __construct(
        private LoginThrottleStoreInterface $store,
        private int $maxAttempts = 5,
        private int $baseLockSeconds = 60,
        private int $lockMultiplier = 2,
        private int $maxLockSeconds = 3600,
        private int $memorySeconds = 86400
    ) {}

    /**
     * Corta el flujo si el origen esta bloqueado. Se llama ANTES de tocar la base de datos.
     *
     * @throws TooManyLoginAttemptsException
     */
    public function assertNotLocked(LoginAttemptKey $key): void
    {
        $state = $this->store->get($key->value());

        if ($state === null) {
            return;
        }

        $now = $this->now();

        if ($state->isLockedAt($now)) {
            throw new TooManyLoginAttemptsException($state->secondsUntilUnlock($now));
        }
    }

    /**
     * Registra un intento fallido. Si con este se agota el limite, bloquea y lanza.
     *
     * @throws TooManyLoginAttemptsException
     */
    public function registerFailure(LoginAttemptKey $key): void
    {
        $now   = $this->now();
        $state = $this->store->get($key->value()) ?? LoginThrottleState::empty();

        // Si venia de un bloqueo ya caducado, empieza ventana nueva pero conservando el nivel:
        // eso es lo que hace que el proximo bloqueo sea mas largo que el anterior.
        if ($state->lockHasExpiredAt($now)) {
            $state = new LoginThrottleState(0, $state->lockLevel, null);
        }

        $failures = $state->failures + 1;

        if ($failures >= $this->maxAttempts) {
            $level   = min($state->lockLevel + 1, self::MAX_LOCK_LEVEL);
            $seconds = $this->lockSecondsForLevel($level);

            $this->store->put(
                $key->value(),
                new LoginThrottleState(0, $level, $now + $seconds),
                $seconds + $this->memorySeconds
            );

            throw new TooManyLoginAttemptsException($seconds);
        }

        $this->store->put(
            $key->value(),
            new LoginThrottleState($failures, $state->lockLevel, null),
            $this->memorySeconds
        );
    }

    /**
     * Login correcto: el origen deja de ser sospechoso y pierde tambien el historial.
     */
    public function clear(LoginAttemptKey $key): void
    {
        $this->store->forget($key->value());
    }

    /**
     * 1er bloqueo 60s, 2do 120s, 3er 240s... hasta el techo de $maxLockSeconds.
     */
    private function lockSecondsForLevel(int $level): int
    {
        $exponent = max(0, min($level, self::MAX_LOCK_LEVEL) - 1);
        $seconds  = $this->baseLockSeconds * ($this->lockMultiplier ** $exponent);

        return (int) min($seconds, $this->maxLockSeconds);
    }

    private function now(): int
    {
        return time();
    }
}
