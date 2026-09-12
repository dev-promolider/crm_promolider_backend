<?php

namespace App\Console\Commands;

use App\Services\MLM\BinaryCutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * El bono generacional de un lote, por separado del corte.
 *
 * Por defecto el generacional sale dentro del corte, porque es un porcentaje de lo
 * que el corte acaba de pagar y separarlo del todo abre la puerta a que alguien
 * ejecute el corte y nadie lance el segundo paso. Este comando existe para las dos
 * situaciones en que si hace falta a mano: cuando el equipo desacopla los dos pasos
 * desde el panel, y cuando un lote antiguo se quedo sin generacional.
 *
 * Si el lote ya lo tiene pagado, no hace nada.
 */
class DeliverGenerationalBonusCommand extends Command
{
    protected $signature = 'mlm:bono-generacional {--lote= : Lote del corte al que corresponde}';

    protected $description = 'Entrega el bono generacional de un lote de corte que no lo tenga';

    public function handle(BinaryCutService $corte)
    {
        $lote = (int) $this->option('lote');

        if ($lote < 1) {
            $this->error('Hace falta indicar el lote: php artisan mlm:bono-generacional --lote=88');

            return 1;
        }

        $total = DB::transaction(function () use ($corte, $lote) {
            return $corte->payGenerationalForBatch($lote);
        });

        if ($total <= 0) {
            $this->line('No había nada que entregar: el lote ya tenía el generacional pagado, o no pagó bono binario.');

            return 0;
        }

        $this->info(sprintf('Bono generacional del lote %d entregado: $%s.', $lote, number_format($total, 2)));

        return 0;
    }
}
