<?php

namespace App\Console\Commands;

use App\Exceptions\MLM\BinaryCutAlreadyRunException;
use App\Services\MLM\BinaryCutPeriodService;
use Illuminate\Console\Command;
use Promolider\Application\Wallet\UseCases\BinaryCut\ExecuteBinaryCutUseCase;

/**
 * El corte binario a mano, desde la consola.
 *
 * Sirve sobre todo para el primer corte real, que hay que hacer mirando: como nadie
 * consume volumen desde diciembre de 2025, el primer corte paga sobre todo lo
 * acumulado desde el origen de la red.
 */
class RunBinaryCutCommand extends Command
{
    protected $signature = 'mlm:corte-binario
                            {--forzar : Repetir el corte de un periodo que ya se corto}
                            {--estado : Solo mostrar el calendario, sin ejecutar nada}';

    protected $description = 'Ejecuta el corte binario del periodo en curso, una sola vez por periodo';

    public function handle(ExecuteBinaryCutUseCase $executeCut, BinaryCutPeriodService $periodos)
    {
        $periodo = $periodos->clavePeriodo();

        $this->line('');
        $this->info('Periodo en curso: ' . $periodo);
        $this->line('  Habilitado desde: ' . $periodos->inicioDelPeriodo()->toDateTimeString());
        $this->line('  Ya ejecutado:     ' . ($periodos->periodoEjecutado($periodo) ? 'sí' : 'no'));
        $this->line('  Próximo corte:    ' . $periodos->proximoCorte()->toDateTimeString());
        $this->line('');

        if ($this->option('estado')) {
            $historial = $periodos->historial(6);

            if ($historial->isNotEmpty()) {
                $this->table(
                    ['Periodo', 'Lote', 'Ejecutado', 'Pagados', 'Binario', 'Generacional', 'Forzado'],
                    $historial->map(function ($fila) {
                        return [
                            $fila->period_key,
                            $fila->batch,
                            $fila->executed_at,
                            $fila->users_paid,
                            number_format((float) $fila->total_binary, 2),
                            number_format((float) $fila->total_generational, 2),
                            $fila->forced ? 'sí' : '',
                        ];
                    })->all()
                );
            }

            return 0;
        }

        if ($this->option('forzar') && !$this->confirm('Vas a repetir un corte ya ejecutado. El volumen de ese periodo ya se consumió. ¿Continuar?', false)) {
            $this->warn('Cancelado.');

            return 0;
        }

        try {
            $resumen = $executeCut->execute((bool) $this->option('forzar'));
        } catch (BinaryCutAlreadyRunException $e) {
            $this->error($e->getMessage());
            $this->line('Si de verdad hace falta repetirlo: php artisan mlm:corte-binario --forzar');

            return 1;
        }

        $this->info(sprintf(
            'Corte %s terminado. Lote %d · %d pagados · $%s de bono binario · $%s de generacional.',
            $resumen['periodo'],
            $resumen['lote'],
            $resumen['pagados'],
            number_format($resumen['total_binario'], 2),
            number_format($resumen['total_generacional'], 2)
        ));

        return 0;
    }
}
