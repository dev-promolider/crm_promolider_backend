<?php
namespace Promolider\Domain\Auth\Ports\Out;

use Promolider\Domain\Auth\ValueObjects\LoginThrottleState;

/**
 * Puerto de salida hacia el almacen de intentos de login.
 *
 * Solo lee, escribe y olvida estado con un TTL. Las reglas de
 * negocio (cuantos intentos, cuanto dura el bloqueo, como escala) 
 * viven en el dominio, en LoginThrottle, no en el adaptador.
 */
interface LoginThrottleStoreInterface
{
    public function get(string $key): ?LoginThrottleState;

    public function put(string $key, LoginThrottleState $state, int $ttlSeconds): void;

    public function forget(string $key): void;
}
