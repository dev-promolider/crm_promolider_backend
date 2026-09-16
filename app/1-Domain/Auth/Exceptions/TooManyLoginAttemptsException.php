<?php
namespace Promolider\Domain\Auth\Exceptions;

use Exception;

/**
 * El origen agoto el limite de intentos y esta bloqueado.
 *
 * Vive en Dominio, y no en Application junto al resto de excepciones del proyecto,
 * porque quien la lanza es un servicio de dominio (LoginThrottle): hacerla depender
 * de la capa de aplicacion invertiria la direccion de las dependencias.
 *
 * No lleva mensaje traducido a proposito: la traduccion es responsabilidad del
 * adaptador de entrada (AuthController), no del dominio.
 */
class TooManyLoginAttemptsException extends Exception
{
    private int $retryAfterSeconds;

    public function __construct(int $retryAfterSeconds)
    {
        $this->retryAfterSeconds = max(1, $retryAfterSeconds);

        parent::__construct('Too Many Requests', 429);
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
