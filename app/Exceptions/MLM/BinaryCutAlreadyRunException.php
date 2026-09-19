<?php

namespace App\Exceptions\MLM;

use DateTimeInterface;
use RuntimeException;
use Throwable;

/**
 * El periodo pedido ya tiene corte. Se lanza antes de tocar nada, para que el
 * segundo intento no llegue a pagar ni a repartir rangos.
 */
class BinaryCutAlreadyRunException extends RuntimeException
{
    public string $periodo;
    public ?string $ejecutadoEl;

    public function __construct(
        string $periodo,
        string|DateTimeInterface|null $ejecutadoEl = null,
        int $code = 409,
        ?Throwable $previous = null
    ) {
        $this->periodo = $periodo;
        $this->ejecutadoEl = $ejecutadoEl instanceof DateTimeInterface
            ? $ejecutadoEl->format('Y-m-d H:i:s')
            : $ejecutadoEl;

        $cuando = $this->ejecutadoEl ? " (se ejecuto el {$this->ejecutadoEl})" : '';

        parent::__construct(
            "El corte binario del periodo {$periodo} ya se ejecuto{$cuando}. " .
            'Los rangos son mensuales y el volumen ya se consumio, asi que repetirlo pagaria dos veces.',
            $code,
            $previous
        );
    }
}
