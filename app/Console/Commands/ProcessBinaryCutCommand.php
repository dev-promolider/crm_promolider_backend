<?php

namespace App\Console\Commands;

use App\Exceptions\MLM\BinaryCutAlreadyRunException;
use App\Services\MLM\BinaryCutPeriodService;
use App\Services\MLM\PlanSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Wallet\UseCases\BinaryCut\CancelBinaryCutScheduleUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\ExecuteBinaryCutUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\GetBinaryCutScheduleUseCase;

/**
 * El disparador del corte binario. Corre cada minuto y decide si toca o no.
 *
 * Atiende dos cosas distintas:
 *
 *   1. El calendario del plan. El documento que se le entrega al afiliado dice que
 *      el corte es "todos los dias 21 de cada mes a las 12:00 PM (hora Lima, Peru)",
 *      y hasta ahora eso no estaba en ninguna parte: el corte salia cuando alguien
 *      se acordaba de pulsar el boton. Ocho meses sin corte vienen de ahi.
 *   2. La programacion puntual que el administrador fija desde el panel.
 *
 * La comprobacion es contra el periodo, no contra el instante: si el servidor esta
 * caido a las 12:00 del dia 21, el periodo sigue sin cortar y el corte sale en cuanto
 * vuelva. Y si sale por las dos vias a la vez, la segunda choca contra el indice
 * unico de binary_cut_runs y no paga nada.
 */
class ProcessBinaryCutCommand extends Command
{
    protected $signature = 'binarycut:process';

    protected $description = 'Ejecuta el corte binario cuando toca por calendario o por programación puntual';

    public function handle(
        GetBinaryCutScheduleUseCase $getSchedule,
        ExecuteBinaryCutUseCase $executeCut,
        CancelBinaryCutScheduleUseCase $cancelSchedule,
        BinaryCutPeriodService $periodos,
        PlanSettings $settings
    ) {
        $programado = $getSchedule->execute();
        $motivo = null;

        if ($programado && Carbon::now($settings->zonaHoraria())->greaterThanOrEqualTo(Carbon::parse($programado, $settings->zonaHoraria()))) {
            $motivo = "programación puntual del {$programado}";
        } elseif ($periodos->toca()) {
            $motivo = 'calendario del plan (' . $periodos->clavePeriodo() . ')';
        }

        if ($motivo === null) {
            return 0;
        }

        Log::info("[CORTE BINARIO] Disparado por {$motivo}");

        try {
            $resumen = $executeCut->execute();

            if ($programado) {
                $cancelSchedule->execute();
            }

            Log::info('[CORTE BINARIO] Completado desde el programador', $resumen);
            $this->info(sprintf(
                'Corte %s ejecutado: lote %d, %d pagados, $%s de binario y $%s de generacional.',
                $resumen['periodo'],
                $resumen['lote'],
                $resumen['pagados'],
                number_format($resumen['total_binario'], 2),
                number_format($resumen['total_generacional'], 2)
            ));

            return 0;
        } catch (BinaryCutAlreadyRunException $e) {
            // No es un fallo: es la garantia haciendo su trabajo. Si venia de una
            // programacion puntual se limpia, para que no lo reintente cada minuto.
            if ($programado) {
                $cancelSchedule->execute();
            }

            Log::info('[CORTE BINARIO] No se repite: ' . $e->getMessage());

            return 0;
        } catch (\Exception $e) {
            Log::error('[CORTE BINARIO] Error desde el programador: ' . $e->getMessage());
            $this->error('Falló la ejecución del corte binario: ' . $e->getMessage());

            return 1;
        }
    }
}
