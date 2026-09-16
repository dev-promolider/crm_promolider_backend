<?php

namespace App\Services\MLM;

use App\Models\RankBonus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * El bono mensual de rango.
 *
 * La columna monthly_bonus de rank_bonus lleva anos configurada —Sub Director 1000,
 * Director 2000, Decano 4000...— y no se pagaba en ningun sitio: ni el sistema nuevo
 * ni el monolito la tocaban. La auditoria lo dio por una decision de negocio
 * pendiente; el documento del plan que entrega la empresa lo deja cerrado, porque lo
 * promete con estas palabras: "manteniendote en este rango por 3 meses consecutivos
 * se activa una bonificacion mensual de 2,000 dolares hasta que cambies de rango".
 *
 * Que rango tuvo cada quien en cada mes lo dice RankHistoryService, que es tambien
 * quien se lo dice al corte para la antiguedad por rango: la misma pregunta, una
 * sola respuesta.
 *
 * Va por su cuenta, con su propio periodo mensual, y no dentro del corte binario.
 * Esa es justo la separacion que hacia falta: el rango es mensual aunque el corte
 * llegue a ser quincenal, asi que aunque en un mes haya dos cortes este bono se
 * entrega una sola vez. Lo garantiza el indice unico (user_id, period_key).
 */
class RankMonthlyBonusService
{
    private const BONUS_TYPE_RANGO_MENSUAL = 7;

    /** @var array<string, int> mes => ultimo lote de corte de ese mes */
    private array $ultimoLotePorMes = [];

    public function __construct(
        private PlanSettings $settings,
        private BinaryCutPeriodService $periodos,
        private RankHistoryService $historial
    ) {
    }

    /**
     * @param  string|null  $periodo  Mes en formato Y-m. Por defecto, el mes en curso.
     * @param  bool         $simular  Calcula y devuelve el detalle sin pagar nada.
     *
     * @return array{periodo: string, pagados: int, total: float, detalle: array<int, array<string, mixed>>}
     */
    public function execute(?string $periodo = null, bool $simular = false): array
    {
        $historial = $this->historial->historial();
        $rangosPorMes = $historial['rangos'];
        $this->ultimoLotePorMes = $historial['lotes'];

        // Sin mes indicado se premia el ultimo mes que llego a cortarse. Asi el
        // proceso del dia 1 premia el mes que acaba de cerrar sin tener que calcular
        // fechas, y lanzarlo a mano un dia cualquiera hace lo esperable.
        if (!$periodo) {
            $enCurso = $this->periodos->clavePeriodoMensual();
            $conCorte = array_keys($rangosPorMes);
            sort($conCorte);

            $periodo = isset($rangosPorMes[$enCurso]) ? $enCurso : (string) (end($conCorte) ?: $enCurso);
        }

        if (!isset($rangosPorMes[$periodo])) {
            Log::info('[BONO DE RANGO] El mes no tiene ningun corte, no hay rangos que premiar', [
                'periodo' => $periodo,
            ]);

            return ['periodo' => $periodo, 'pagados' => 0, 'total' => 0.0, 'detalle' => []];
        }

        $rangos = RankBonus::all()->keyBy('id');
        $detalle = [];
        $total = 0.0;

        foreach ($rangosPorMes[$periodo] as $userId => $rankId) {
            $rango = $rangos->get($rankId);

            if (!$rango || (float) $rango->monthly_bonus <= 0) {
                continue;
            }

            $usuario = User::find($userId);

            if (!$usuario || !$usuario->active || !$usuario->membershipActive) {
                continue;
            }

            $racha = $this->historial->racha(
                $rangosPorMes,
                $periodo,
                (int) $userId,
                fn (int $rangoId) => $rangoId === (int) $rankId
            );
            $exigidos = max(1, (int) $rango->monthly_bonus_months);

            if ($racha < $exigidos) {
                continue;
            }

            // El plan da el bono mensual desde que se cumple la racha, salvo en el
            // rango mas alto, donde lo promete trimestral. Se paga en el mes en que
            // se alcanza la racha y cada tres meses a partir de ahi.
            if ($rango->monthly_bonus_frequency === 'quarterly' && (($racha - $exigidos) % 3) !== 0) {
                continue;
            }

            $importe = round((float) $rango->monthly_bonus, 2);

            if (!$simular && !$this->pagar((int) $userId, (int) $rankId, $periodo, $importe, $racha)) {
                continue;
            }

            $detalle[] = [
                'user_id' => (int) $userId,
                'usuario' => $usuario->username ?? $usuario->email,
                'rango'   => $rango->name,
                'racha'   => $racha,
                'importe' => $importe,
            ];

            $total += $importe;
        }

        $resumen = [
            'periodo' => $periodo,
            'pagados' => count($detalle),
            'total'   => round($total, 2),
            'detalle' => $detalle,
        ];

        Log::info($simular ? '[BONO DE RANGO] Simulacion' : '[BONO DE RANGO] Entregado', [
            'periodo' => $periodo,
            'pagados' => $resumen['pagados'],
            'total'   => $resumen['total'],
        ]);

        return $resumen;
    }

    /**
     * @return bool  true si se ha llegado a pagar; false si el mes ya estaba pagado
     *               o el usuario no tiene billetera.
     */
    private function pagar(int $userId, int $rankId, string $periodo, float $importe, int $racha): bool
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            Log::warning('[BONO DE RANGO] Usuario sin billetera, se omite', ['user_id' => $userId]);

            return false;
        }

        $lote = $this->ultimoLotePorMes[$periodo] ?? 0;

        try {
            DB::transaction(function () use ($wallet, $userId, $rankId, $periodo, $importe, $racha, $lote) {
                // Si el mes ya estaba pagado, el indice unico corta aqui y no se llega
                // a crear el movimiento. Es la misma garantia que la del corte binario.
                DB::table('rank_monthly_bonus_payments')->insert([
                    'user_id'       => $userId,
                    'rank_bonus_id' => $rankId,
                    'period_key'    => $periodo,
                    'amount'        => $importe,
                    'streak'        => $racha,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                $movimiento = new WalletMovements();
                $movimiento->wallet_id = $wallet->id;
                $movimiento->amount = $importe;
                $movimiento->type = 1;
                $movimiento->status = 1;
                $movimiento->reason = 'Bono mensual de rango ' . $periodo;
                $movimiento->batch = $lote;
                $movimiento->bonus_type_id = self::BONUS_TYPE_RANGO_MENSUAL;
                $movimiento->save();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // 23000 es la violacion del indice unico: ese usuario ya cobro este mes.
            // Que se repita no es un error, es la garantia funcionando; se salta y
            // el resto de la entrega sigue su curso.
            if ($e->getCode() === '23000') {
                Log::info('[BONO DE RANGO] El usuario ya tenia el mes pagado, se omite', [
                    'user_id' => $userId,
                    'periodo' => $periodo,
                ]);

                return false;
            }

            throw $e;
        }

        return true;
    }
}
