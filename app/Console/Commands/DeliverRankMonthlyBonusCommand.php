<?php

namespace App\Console\Commands;

use App\Services\MLM\RankMonthlyBonusService;
use Illuminate\Console\Command;

/**
 * El bono mensual de rango, aparte del corte y siempre una vez al mes.
 *
 * Esta es la separacion que pedia el equipo: el rango es mensual por definicion, y
 * si algun dia el corte binario pasa a ser quincenal, este bono tiene que seguir
 * saliendo una sola vez. Por eso tiene su propio periodo y su propio registro.
 */
class DeliverRankMonthlyBonusCommand extends Command
{
    protected $signature = 'mlm:bono-rango
                            {--periodo= : Mes en formato AAAA-MM. Por defecto, el mes en curso}
                            {--simular : Calcular y mostrar a quién le tocaría, sin pagar nada}';

    protected $description = 'Entrega el bono mensual de rango a quienes lo han mantenido los meses seguidos que exige el plan';

    public function handle(RankMonthlyBonusService $servicio)
    {
        $simular = (bool) $this->option('simular');
        $resumen = $servicio->execute($this->option('periodo'), $simular);

        $this->line('');
        $this->info(($simular ? 'Simulación · ' : 'Entrega · ') . 'periodo ' . $resumen['periodo']);

        if (!$resumen['detalle']) {
            $this->line('Nadie cumple los meses seguidos que pide su rango, o el mes no tiene ningún corte.');

            return 0;
        }

        $this->table(
            ['Usuario', 'Rango', 'Meses seguidos', 'Importe'],
            array_map(function ($fila) {
                return [
                    $fila['usuario'],
                    $fila['rango'],
                    $fila['racha'],
                    '$' . number_format($fila['importe'], 2),
                ];
            }, $resumen['detalle'])
        );

        $this->info(sprintf('%d personas · $%s en total.', $resumen['pagados'], number_format($resumen['total'], 2)));

        return 0;
    }
}
