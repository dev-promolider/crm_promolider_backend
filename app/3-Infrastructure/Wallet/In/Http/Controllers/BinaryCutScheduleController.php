<?php

namespace Promolider\Infrastructure\Wallet\In\Http\Controllers;

use App\Exceptions\MLM\BinaryCutAlreadyRunException;
use App\Http\Controllers\Controller;
use App\Services\MLM\BinaryCutPeriodService;
use App\Services\MLM\BinaryCutService;
use App\Services\MLM\PlanSettings;
use App\Services\MLM\RankMonthlyBonusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Wallet\UseCases\BinaryCut\CancelBinaryCutScheduleUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\ExecuteBinaryCutUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\GetBinaryCutScheduleUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\ScheduleBinaryCutUseCase;

class BinaryCutScheduleController extends Controller
{
    public function __construct(
        private ScheduleBinaryCutUseCase $scheduleBinaryCutUseCase,
        private GetBinaryCutScheduleUseCase $getBinaryCutScheduleUseCase,
        private CancelBinaryCutScheduleUseCase $cancelBinaryCutScheduleUseCase,
        private ExecuteBinaryCutUseCase $executeBinaryCutUseCase,
        private BinaryCutPeriodService $periodos,
        private PlanSettings $settings
    ) {}

    public function getSchedule()
    {
        $datetime = $this->getBinaryCutScheduleUseCase->execute();
        return response()->json(['scheduled_at' => $datetime]);
    }

    /**
     * El calendario completo del corte: como esta configurado, en que periodo
     * estamos, si ese periodo ya se corto y cuando toca el siguiente.
     *
     * Es lo que hacia falta para que desde el panel se vea de un vistazo que el mes
     * ya esta cerrado, en vez de tener que fiarse de que nadie haya pulsado el boton
     * dos veces.
     */
    public function status()
    {
        $periodo = $this->periodos->clavePeriodo();

        return response()->json([
            'configuracion' => $this->settings->todos(),
            'periodo_actual' => [
                'clave'      => $periodo,
                'mes'        => $this->periodos->clavePeriodoMensual(),
                'habilitado' => $this->periodos->inicioDelPeriodo()->toDateTimeString(),
                'ejecutado'  => $this->periodos->periodoEjecutado($periodo),
            ],
            'proximo_corte'  => $this->periodos->proximoCorte()->toDateTimeString(),
            'zona_horaria'   => $this->settings->zonaHoraria(),
            'programado_en'  => $this->getBinaryCutScheduleUseCase->execute(),
            'historial'      => $this->periodos->historial(),
        ]);
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'datetime' => 'required|date_format:Y-m-d H:i:s'
        ]);

        $this->scheduleBinaryCutUseCase->execute($request->datetime);
        return response()->json(['message' => 'Corte binario programado con éxito.', 'scheduled_at' => $request->datetime]);
    }

    public function cancel()
    {
        $this->cancelBinaryCutScheduleUseCase->execute();
        return response()->json(['message' => 'Programación de corte binario cancelada.']);
    }

    /**
     * Lanza el corte del periodo en curso.
     *
     * Si ese periodo ya se corto responde 409 y no toca nada: los rangos son
     * mensuales y el volumen ya se consumio, asi que repetirlo pagaria dos veces.
     * Repetirlo a proposito exige dos cosas a la vez —forzar y confirmar— para que
     * no salga de un clic de mas.
     */
    public function executeNow(Request $request)
    {
        $request->validate([
            'forzar'   => 'sometimes|boolean',
            'confirmar' => 'sometimes|boolean',
        ]);

        $forzar = $request->boolean('forzar');

        if ($forzar && !$request->boolean('confirmar')) {
            return response()->json([
                'error' => 'Para repetir el corte de un periodo ya cortado hay que confirmarlo expresamente.',
            ], 422);
        }

        try {
            $resumen = $this->executeBinaryCutUseCase->execute($forzar, optional($request->user())->id);

            return response()->json([
                'message' => 'Corte binario ejecutado con éxito.',
                'data'    => $resumen,
            ]);
        } catch (BinaryCutAlreadyRunException $e) {
            return response()->json([
                'error'   => $e->getMessage(),
                'periodo' => $e->periodo,
            ], 409);
        } catch (\Exception $e) {
            Log::error('Error al ejecutar el corte binario: ' . $e->getMessage());

            return response()->json(['error' => 'Error al ejecutar el corte: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bono generacional de un lote, por separado del corte.
     *
     * Solo hace falta si en la configuracion se ha desacoplado del corte, o si un
     * lote antiguo se quedo sin el. Si el lote ya lo tiene pagado, no se repite.
     */
    public function payGenerational(Request $request, BinaryCutService $corte)
    {
        $request->validate(['lote' => 'required|integer|min:1']);

        try {
            $total = DB::transaction(function () use ($corte, $request) {
                return $corte->payGenerationalForBatch((int) $request->input('lote'));
            });

            return response()->json([
                'message' => $total > 0
                    ? 'Bono generacional entregado.'
                    : 'No había nada que entregar: el lote ya tenía el generacional pagado o no pagó binario.',
                'data'    => ['lote' => (int) $request->input('lote'), 'total' => $total],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al pagar el bono generacional: ' . $e->getMessage());

            return response()->json(['error' => 'Error al pagar el bono generacional: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bono mensual de rango del mes indicado. Con simular=true no paga nada y
     * devuelve a quien le tocaria y por que.
     */
    public function payRankBonus(Request $request, RankMonthlyBonusService $servicio)
    {
        $request->validate([
            'periodo' => 'sometimes|regex:/^\d{4}-\d{2}$/',
            'simular' => 'sometimes|boolean',
        ]);

        try {
            $resumen = $servicio->execute($request->input('periodo'), $request->boolean('simular'));

            return response()->json([
                'message' => $request->boolean('simular')
                    ? 'Simulación del bono mensual de rango.'
                    : 'Bono mensual de rango entregado.',
                'data'    => $resumen,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en el bono mensual de rango: ' . $e->getMessage());

            return response()->json(['error' => 'Error en el bono mensual de rango: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Configuracion del corte: frecuencia, dia, hora y zona horaria.
     *
     * El documento del plan dice "todos los dias 21 de cada mes a las 12:00 PM (hora
     * Lima, Peru)", y el equipo quiere poder pasarlo a quincenal sin tocar codigo.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'binary_cut_frequency' => 'sometimes|in:monthly,biweekly',
            'binary_cut_day'       => 'sometimes|integer|min:1|max:28',
            'binary_cut_time'      => 'sometimes|regex:/^\d{1,2}:\d{2}$/',
            'binary_cut_timezone'  => 'sometimes|timezone',
            'binary_cut_automatic' => 'sometimes|boolean',
            'binary_cut_pay_generational_inline' => 'sometimes|boolean',
            'generational_university_from'       => 'sometimes|integer|min:0|max:8',
        ]);

        foreach ($request->only([
            PlanSettings::CORTE_FRECUENCIA,
            PlanSettings::CORTE_DIA,
            PlanSettings::CORTE_HORA,
            PlanSettings::CORTE_ZONA,
            PlanSettings::CORTE_AUTOMATICO,
            PlanSettings::GENERACIONAL_EN_EL_CORTE,
            PlanSettings::GENERACIONAL_UNIVERSITY_DESDE,
        ]) as $clave => $valor) {
            if (is_bool($valor)) {
                $valor = $valor ? '1' : '0';
            }

            $this->settings->set($clave, (string) $valor);
        }

        return response()->json([
            'message' => 'Configuración del corte actualizada.',
            'data'    => $this->settings->todos(),
        ]);
    }
}
