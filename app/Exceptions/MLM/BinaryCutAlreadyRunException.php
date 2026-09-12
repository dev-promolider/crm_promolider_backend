<?php

namespace App\Exceptions\MLM;

use RuntimeException;

/**
 * El periodo pedido ya tiene corte. Se lanza antes de tocar nada, para que el
 * segundo intento no llegue a pagar ni a repartir rangos.
 */
class BinaryCutAlreadyRunException extends RuntimeException
{
    public function __construct(
        public readonly string $periodo,
        public readonly ?string $ejecutadoEl = null
    ) {
        $cuando = $ejecutadoEl ? " (se ejecuto el {$ejecutadoEl})" : '';

        parent::__construct(
            "El corte binario del periodo {$periodo} ya se ejecuto{$cuando}. " .
            'Los rangos son mensuales y el volumen ya se consumio, asi que repetirlo pagaria dos veces.'
        );
    }
}
