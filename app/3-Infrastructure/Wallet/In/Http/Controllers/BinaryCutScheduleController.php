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
     * El calendario completo del corte: cómo está configurado, en qué periodo estamos,
     * si ese periodo ya se cortó y cuándo toca el siguiente.
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
     * Simula el corte del periodo en curso sin pagar nada.
     *
     * Devuelve a quién le pagaría, cuánto y por qué, y quién se queda fuera teniendo
     * puntos en las dos piernas. Es el paso que faltaba antes de confirmar: el primer
     * corte real se lanzó con un solo clic.
     */
    public function simulate(BinaryCutService $corte)
    {
        try {
            return response()->json(['data' => $corte->simular()]);
        } catch (\Exception $e) {
            Log::error('Error al simular el corte binario: ' . $e->getMessage());

            return response()->json(['error' => 'No se pudo simular el corte: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lanza el corte del periodo en curso.
     *
     * Si ese periodo ya se cortó responde 409 y no toca nada. Repetirlo a propósito
     * exige dos cosas a la vez —forzar y confirmar— para que no salga de un clic de más.
     */
    public function executeNow(Request $request)
    {
        $request->validate([
            'forzar'    => 'sometimes|boolean',
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
     * Todos los cortes registrados, del más reciente al más antiguo.
     */
    public function runs()
    {
        $cortes = DB::table('binary_cut_runs as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.executed_by')
            ->whereNotNull('c.executed_at')
            ->orderByDesc('c.executed_at')
            ->orderByDesc('c.id')
            ->get(['c.*', 'u.username as ejecutado_por']);

        return response()->json(['data' => $cortes]);
    }

    /**
     * Quién ganó en un corte y por qué.
     *
     * Los cortes registrados desde esta versión guardan el porcentaje, lo calculado y
     * si se topó. Los anteriores solo tienen piernas e importe: lo que falta se deja
     * vacío en lugar de reconstruirlo con la configuración de hoy, que pudo cambiar.
     */
    public function winners(int $batch)
    {
        $corte = DB::table('binary_cut_runs as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.executed_by')
            ->where('c.batch', $batch)
            ->orderBy('c.id')
            ->first(['c.*', 'u.username as ejecutado_por']);

        $generacional = DB::table('wallet_movements')
            ->join('wallet', 'wallet.id', '=', 'wallet_movements.wallet_id')
            ->where('wallet_movements.batch', $batch)
            ->where('wallet_movements.bonus_type_id', 5)
            ->select('wallet.user_id', DB::raw('SUM(wallet_movements.amount) as total'))
            ->groupBy('wallet.user_id')
            ->pluck('total', 'user_id');

        $filas = DB::table('binary_cut_histories as h')
            ->leftJoin('users as u', 'u.id', '=', 'h.user_id')
            ->leftJoin('account_type as a', function ($join) {
                $join->on('a.id', '=', DB::raw('COALESCE(h.account_type_id, u.id_account_type)'));
            })
            ->leftJoin('rank_bonus as r', 'r.id', '=', 'h.rank_id')
            ->where('h.batch', $batch)
            ->orderByDesc('h.transferred_amount')
            ->get([
                'h.*', 'u.username', 'u.name', 'u.last_name', 'u.photo',
                'a.account as membresia', 'r.name as rango', 'r.icon as rango_icono', 'r.max_pay',
            ])
            ->map(function ($f) use ($generacional) {
                $izquierda = (float) $f->left_points;
                $derecha = (float) $f->right_points;
                $conDetalle = $f->pay_percentage !== null;

                return [
                    'user_id'        => (int) $f->user_id,
                    'usuario'        => $f->username,
                    'nombre'         => trim(($f->name ?? '') . ' ' . ($f->last_name ?? '')),
                    'foto'           => $f->photo,
                    'membresia'      => $f->membresia,
                    'rango'          => $f->rango,
                    'rango_icono'    => $f->rango_icono,
                    'izquierda'      => $izquierda,
                    'derecha'        => $derecha,
                    'pierna_de_pago' => min($izquierda, $derecha),
                    'porcentaje'     => $conDetalle ? (float) $f->pay_percentage : null,
                    'calculado'      => $conDetalle ? (float) $f->calculated_amount : null,
                    'tope'           => $f->max_pay !== null ? (float) $f->max_pay : null,
                    'topado'         => $conDetalle ? (bool) $f->capped : null,
                    'pagado'         => (float) $f->transferred_amount,
                    'remanente'      => $f->carryover_points !== null ? (float) $f->carryover_points : abs($izquierda - $derecha),
                    'lado_remanente' => $f->carryover_side === null ? null : ((int) $f->carryover_side === 0 ? 'izquierda' : 'derecha'),
                    'generacional'   => round((float) ($generacional->get($f->user_id) ?? 0), 2),
                    'con_detalle'    => $conDetalle,
                ];
            });

        return response()->json([
            'data' => [
                'corte'              => $corte,
                'lote'               => $batch,
                'ganadores'          => $filas,
                'total_binario'      => round($filas->sum('pagado'), 2),
                'total_generacional' => round($filas->sum('generacional'), 2),
            ],
        ]);
    }

    /**
     * Bono generacional de un lote, por separado del corte.
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
     * Bono mensual de rango del mes indicado. Con simular=true no paga nada.
     */
    public function payRankBonus(Request $request, RankMonthlyBonusService $servicio)
    {
        $request->validate([
            'periodo' => 'sometimes|nullable|regex:/^\d{4}-\d{2}$/',
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
     * Configuración del corte: frecuencia, día, hora, zona horaria, disparo automático
     * y si el generacional va dentro del corte.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            PlanSettings::CORTE_FRECUENCIA              => 'sometimes|in:monthly,biweekly',
            PlanSettings::CORTE_DIA                     => 'sometimes|integer|min:1|max:28',
            PlanSettings::CORTE_HORA                    => 'sometimes|regex:/^\d{1,2}:\d{2}$/',
            PlanSettings::CORTE_ZONA                    => 'sometimes|timezone',
            PlanSettings::CORTE_AUTOMATICO              => 'sometimes|boolean',
            PlanSettings::GENERACIONAL_EN_EL_CORTE      => 'sometimes|boolean',
            PlanSettings::GENERACIONAL_NIVEL_ALTO_DESDE => 'sometimes|integer|min:0|max:30',
        ]);

        foreach ($request->only([
            PlanSettings::CORTE_FRECUENCIA,
            PlanSettings::CORTE_DIA,
            PlanSettings::CORTE_HORA,
            PlanSettings::CORTE_ZONA,
            PlanSettings::CORTE_AUTOMATICO,
            PlanSettings::GENERACIONAL_EN_EL_CORTE,
            PlanSettings::GENERACIONAL_NIVEL_ALTO_DESDE,
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
