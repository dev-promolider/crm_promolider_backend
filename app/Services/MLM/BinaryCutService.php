<?php

namespace App\Services\MLM;

use App\Exceptions\MLM\BinaryCutAlreadyRunException;
use App\Models\BinaryCutHistory;
use App\Models\Option;
use App\Models\Point;
use App\Models\RankBonus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * El corte binario.
 *
 * Trabaja sobre la tabla 'points', que es exactamente la que el afiliado ve en
 * «Puntos Binarios Activos» y en el arbol. El volumen se consume: los puntos que
 * entran en el corte quedan en status 0 y el sobrante de la pierna mayor se guarda
 * como una unica fila de arrastre.
 *
 * El corte pertenece a un periodo. La fila de binary_cut_runs se inserta dentro de
 * la misma transaccion que los pagos, y su indice unico sobre period_key impide
 * repetirlo aunque lleguen dos peticiones a la vez.
 *
 * Y ahora se puede simular: el corte se ejecuta entero dentro de una transaccion que
 * se deshace al terminar, y devuelve a quien le pagaria, cuanto y por que, ademas de
 * quien se queda fuera teniendo puntos en las dos piernas. El primer corte real se
 * lanzo sin ningun paso previo y el ingeniero tuvo que preguntar despues que habia
 * calculado; con la simulacion eso se ve antes de confirmar.
 */
class BinaryCutService
{
    private const BONUS_TYPE_BINARIO = 4;
    private const BONUS_TYPE_GENERACIONAL = 5;

    /** @var array<int, array{left: float, right: float}> */
    private array $volumes = [];

    /** @var array<int, array<int>> hijos unilevel por patrocinador */
    private array $unilevelChildren = [];

    /** @var array<int, User> */
    private array $usersById = [];

    /** @var array<int, array<int>> descendientes activos ya calculados */
    private array $descendantsCache = [];

    /** @var array<int, float> generacional cobrado en este corte, por usuario */
    private array $generacionalPorUsuario = [];

    private string $etiqueta = '[CORTE BINARIO]';

    public function __construct(
        private PlanSettings $settings,
        private BinaryCutPeriodService $periodos,
        private MembershipRules $membresias
    ) {
    }

    /**
     * @param  bool      $forzar        Repetir el corte de un periodo ya cortado. Solo
     *                                  administrador, y queda marcado en el historial.
     * @param  int|null  $ejecutadoPor  Quien lo lanza; nulo si viene del programador.
     *
     * @return array{lote: int, periodo: string, pagados: int, total_binario: float, total_generacional: float, detalle: array, excluidos: array}
     *
     * @throws BinaryCutAlreadyRunException
     */
    public function execute(bool $forzar = false, ?int $ejecutadoPor = null): array
    {
        // El servicio guarda mapas en memoria para no repetir consultas. Si se le llama
        // dos veces (dos cortes seguidos), hay que partir de cero: si no, el segundo
        // corte trabajaria con el volumen del primero y volveria a pagarlo.
        $this->volumes = [];
        $this->unilevelChildren = [];
        $this->usersById = [];
        $this->descendantsCache = [];
        $this->generacionalPorUsuario = [];

        $periodo = $this->periodos->clavePeriodo();
        $claveFila = $this->reservarPeriodo($periodo, $forzar, $ejecutadoPor);

        $batchOption = Option::firstOrCreate(['description' => 'batch'], ['value' => '1']);
        $batch = (int) $batchOption->value;

        Log::info($this->etiqueta . ' Iniciando', ['lote' => $batch, 'periodo' => $claveFila]);

        // Solo rangos activos: al retirar uno desde el panel deja de asignarse, pero
        // no se borra, porque rank_binary y binary_cut_histories lo referencian.
        $ranks = RankBonus::where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('vol_min')
            ->orderBy('id')
            ->get();

        if ($ranks->isEmpty()) {
            throw new \RuntimeException('No hay rangos activos configurados en rank_bonus.');
        }

        $this->loadUsers();
        $this->loadVolumes();

        $paidAmounts = [];
        $ranksByUser = [];
        $detalle = [];
        $excluidos = [];
        $totalBinario = 0.0;

        foreach ($this->volumes as $userId => $volume) {
            $left = (float) $volume['left'];
            $right = (float) $volume['right'];
            $min = min($left, $right);
            $max = max($left, $right);

            $user = $this->usersById[$userId] ?? null;
            $motivo = $this->motivoDeExclusion($user);

            if ($motivo !== null) {
                // Solo interesa explicar a quien tenia algo que cobrar: puntos en las
                // dos piernas. El resto no habria cobrado igualmente.
                if ($min > 0) {
                    $excluidos[] = $this->filaExcluido($userId, $user, $left, $right, $motivo);
                }
                continue;
            }

            // Sin pierna menor no hay nada que pagar, y por tanto nada que consumir:
            // los puntos siguen activos para el corte siguiente.
            if ($min <= 0) {
                continue;
            }

            $rank = $this->resolveRank($user, $min, $ranks);
            $ranksByUser[$userId] = $rank;

            DB::table('rank_binary')->insert([
                'user_id'    => $userId,
                'rank_id'    => $rank->id,
                'batch'      => $batch,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = Wallet::where('user_id', $userId)->first();

            if (!$wallet) {
                Log::warning($this->etiqueta . ' Usuario sin billetera, se omite', ['user_id' => $userId]);
                $excluidos[] = $this->filaExcluido($userId, $user, $left, $right, 'No tiene billetera');
                continue;
            }

            $payInBinary = (float) ($user->accountType->pay_in_binary ?? 0);
            $calculado = round($min * ($payInBinary / 100), 2);
            $amount = $calculado;
            $topado = false;

            if ($amount > (float) $rank->max_pay) {
                Log::info($this->etiqueta . ' Bono topado por rango', [
                    'user_id'   => $userId,
                    'calculado' => $amount,
                    'tope'      => $rank->max_pay,
                ]);
                $amount = (float) $rank->max_pay;
                $topado = true;
            }

            $remanente = round($max - $min, 2);
            $ladoRemanente = $remanente > 0 ? ($left > $right ? 0 : 1) : null;

            $this->consumePoints($userId, $left, $right, $min, $max);

            if ($amount > 0) {
                $movement = new WalletMovements();
                $movement->wallet_id = $wallet->id;
                $movement->amount = $amount;
                $movement->type = 1;
                $movement->status = 1;
                $movement->reason = 'Bono binario';
                $movement->batch = $batch;
                $movement->bonus_type_id = self::BONUS_TYPE_BINARIO;
                $movement->save();

                $paidAmounts[$userId] = $amount;
                $totalBinario += $amount;
            }

            BinaryCutHistory::create([
                'user_id'            => $userId,
                'rank_id'            => $rank->id,
                'account_type_id'    => $user->id_account_type,
                'left_points'        => $left,
                'right_points'       => $right,
                'pay_percentage'     => $payInBinary,
                'calculated_amount'  => $calculado,
                'capped'             => $topado,
                'transferred_amount' => $amount,
                'carryover_points'   => $remanente,
                'carryover_side'     => $ladoRemanente,
                'batch'              => $batch,
            ]);

            $detalle[$userId] = [
                'user_id'        => $userId,
                'usuario'        => $user->username,
                'nombre'         => trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')),
                'membresia'      => $user->accountType->account ?? null,
                'rango'          => $rank->name,
                'rango_id'       => (int) $rank->id,
                'izquierda'      => $left,
                'derecha'        => $right,
                'pierna_de_pago' => $min,
                'porcentaje'     => $payInBinary,
                'calculado'      => $calculado,
                'tope'           => (float) $rank->max_pay,
                'topado'         => $topado,
                'pagado'         => $amount,
                'remanente'      => $remanente,
                'lado_remanente' => $ladoRemanente === null ? null : ($ladoRemanente === 0 ? 'izquierda' : 'derecha'),
                'generacional'   => 0.0,
            ];
        }

        $totalGeneracional = 0.0;

        // El generacional es un porcentaje de lo que se acaba de pagar de binario, asi
        // que por defecto va en la misma transaccion. Se puede desacoplar desde el
        // panel, y entonces se lanza aparte con "php artisan mlm:bono-generacional".
        if ($this->settings->get(PlanSettings::GENERACIONAL_EN_EL_CORTE) !== '0') {
            $totalGeneracional = $this->payGenerationalBonuses($paidAmounts, $ranksByUser, $batch);
        }

        foreach ($this->generacionalPorUsuario as $userId => $monto) {
            if (isset($detalle[$userId])) {
                $detalle[$userId]['generacional'] = round($monto, 2);
            }
        }

        $batchOption->value = (string) ($batch + 1);
        $batchOption->save();

        DB::table('binary_cut_runs')->where('period_key', $claveFila)->update([
            'batch'              => $batch,
            'executed_at'        => now(),
            'users_paid'         => count($paidAmounts),
            'total_binary'       => round($totalBinario, 2),
            'total_generational' => round($totalGeneracional, 2),
            'updated_at'         => now(),
        ]);

        $detalle = array_values($detalle);
        usort($detalle, fn ($a, $b) => $b['pagado'] <=> $a['pagado']);
        usort($excluidos, fn ($a, $b) => $b['pierna_de_pago'] <=> $a['pierna_de_pago']);

        $resumen = [
            'lote'               => $batch,
            'periodo'            => $claveFila,
            'pagados'            => count($paidAmounts),
            'total_binario'      => round($totalBinario, 2),
            'total_generacional' => round($totalGeneracional, 2),
        ];

        Log::info($this->etiqueta . ' Terminado', $resumen);

        return $resumen + [
            'detalle'   => $detalle,
            'excluidos' => $excluidos,
        ];
    }

    /**
     * Ejecuta el corte del periodo en curso sin que quede nada: todo ocurre dentro de
     * una transaccion que se deshace al terminar.
     *
     * Ignora la guarda del periodo a proposito, para poder ver tambien que pagaria
     * una repeticion; si el periodo ya estaba cortado lo indica en la respuesta.
     */
    public function simular(): array
    {
        $yaCortado = $this->periodos->periodoEjecutado($this->periodos->clavePeriodo());
        $this->etiqueta = '[SIMULACION DE CORTE]';

        DB::beginTransaction();

        try {
            $resultado = $this->execute(true, null);
        } finally {
            DB::rollBack();
            $this->etiqueta = '[CORTE BINARIO]';
        }

        $resultado['periodo'] = $this->periodos->clavePeriodo();
        $resultado['periodo_ya_cortado'] = $yaCortado;
        $resultado['simulacion'] = true;

        return $resultado;
    }

    /**
     * Por que alguien con volumen no entra en el corte, o null si entra.
     */
    private function motivoDeExclusion(?User $user): ?string
    {
        if (!$user) {
            return 'El usuario ya no existe';
        }

        if (!$user->membershipActive) {
            return 'Membresía vencida o sin aprobar';
        }

        if (!$user->active) {
            return 'OPC vencido';
        }

        if (!$user->qualified) {
            return 'No calificado: le falta un directo activo en alguna pierna';
        }

        return null;
    }

    private function filaExcluido(int $userId, ?User $user, float $left, float $right, string $motivo): array
    {
        return [
            'user_id'        => $userId,
            'usuario'        => $user->username ?? null,
            'nombre'         => $user ? trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')) : null,
            'membresia'      => $user->accountType->account ?? null,
            'izquierda'      => $left,
            'derecha'        => $right,
            'pierna_de_pago' => min($left, $right),
            'motivo'         => $motivo,
        ];
    }

    /**
     * Reserva el periodo antes de tocar dinero.
     *
     * Devuelve la clave con la que ha quedado registrado: la del periodo, o la del
     * periodo con sufijo si el administrador ha forzado una repeticion.
     */
    private function reservarPeriodo(string $periodo, bool $forzar, ?int $ejecutadoPor): string
    {
        $existente = DB::table('binary_cut_runs')->where('period_key', $periodo)->first();

        if ($existente && !$forzar) {
            throw new BinaryCutAlreadyRunException($periodo, $existente->executed_at);
        }

        $clave = $periodo;
        $forzado = false;

        if ($existente) {
            // Un corte forzado no pisa al anterior: entra como repeticion numerada
            // para que el historial siga contando lo que paso de verdad.
            $repeticion = DB::table('binary_cut_runs')
                ->where('period_key', 'like', $periodo . '#%')
                ->count() + 2;

            $clave = $periodo . '#' . $repeticion;
            $forzado = true;

            Log::warning($this->etiqueta . ' Repeticion forzada de un periodo ya cortado', [
                'periodo'    => $periodo,
                'repeticion' => $clave,
                'usuario'    => $ejecutadoPor,
            ]);
        }

        // Si dos peticiones llegan a la vez, la segunda revienta aqui por el indice
        // unico y su transaccion se deshace entera sin haber pagado nada.
        DB::table('binary_cut_runs')->insert([
            'period_key'  => $clave,
            'batch'       => 0,
            'frequency'   => $this->settings->frecuenciaCorte(),
            'executed_by' => $ejecutadoPor,
            'forced'      => $forzado,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return $clave;
    }

    /**
     * Los puntos de alguien son las filas donde figura como sponsor_id.
     */
    private function loadVolumes(): void
    {
        $rows = DB::table('points')
            ->select('sponsor_id', 'side', DB::raw('SUM(points) as total'))
            ->where('status', 1)
            ->groupBy('sponsor_id', 'side')
            ->get();

        foreach ($rows as $row) {
            $id = (int) $row->sponsor_id;

            if (!isset($this->volumes[$id])) {
                $this->volumes[$id] = ['left' => 0.0, 'right' => 0.0];
            }

            $lado = (int) $row->side === 0 ? 'left' : 'right';
            $this->volumes[$id][$lado] = (float) $row->total;
        }
    }

    private function loadUsers(): void
    {
        $users = User::with(['accountType', 'classifiedSponsor.user'])->get();

        foreach ($users as $user) {
            $this->usersById[(int) $user->id] = $user;

            $sponsorId = (int) $user->id_referrer_sponsor;

            if ($sponsorId > 0) {
                $this->unilevelChildren[$sponsorId][] = (int) $user->id;
            }
        }
    }

    /**
     * Marca como consumidos los puntos que entran en este corte y deja el sobrante
     * de la pierna mayor como una sola fila de arrastre.
     */
    private function consumePoints(int $userId, float $left, float $right, float $min, float $max): void
    {
        Point::where('sponsor_id', $userId)->where('status', 1)->update(['status' => 0]);

        $remanente = round($max - $min, 2);

        if ($remanente <= 0) {
            return;
        }

        Point::create([
            'user_id'    => $userId,
            'sponsor_id' => $userId,
            'points'     => $remanente,
            'side'       => $left > $right ? 0 : 1,
            'status'     => 1,
            'reason'     => 'Binary cut',
        ]);
    }

    /**
     * Rango del corte. Ademas del volumen de la pierna de pago exige directos activos
     * y, en los rangos que lo piden, un numero de membresias de nivel alto.
     */
    private function resolveRank(User $user, float $minPoints, $ranks)
    {
        // El sistema lleva desde siempre contando la red entera para directos y para
        // membresias de nivel alto. El interruptor permite pasar a solo directos; es
        // una decision de negocio porque mueve el rango y el tope de todo el mundo.
        $enLaRed = $this->activeDescendants((int) $user->id);
        $directos = $this->activeDirects((int) $user->id);

        $activeCount = count(
            $this->settings->alcanceDirectos() === 'directos' ? $directos : $enLaRed
        );

        $paraNivelAlto = $this->settings->alcanceNivelAlto() === 'directos' ? $directos : $enLaRed;

        $nivelAltoCount = 0;
        foreach ($paraNivelAlto as $descendantId) {
            $descendant = $this->usersById[$descendantId] ?? null;
            if ($descendant && $this->membresias->cuentaComoNivelAlto((int) $descendant->id_account_type)) {
                $nivelAltoCount++;
            }
        }

        $elegido = $ranks->first();

        foreach ($ranks as $rank) {
            $cumple = $minPoints >= (float) $rank->vol_min
                && $activeCount >= (int) $rank->active_direct
                && $nivelAltoCount >= (int) $rank->pack_max;

            if ($cumple) {
                $elegido = $rank;
            }
        }

        return $elegido;
    }

    /**
     * Patrocinados directos que estan activos. Un solo nivel.
     *
     * @return array<int>
     */
    private function activeDirects(int $userId): array
    {
        $activos = [];

        foreach ($this->unilevelChildren[$userId] ?? [] as $id) {
            $hijo = $this->usersById[$id] ?? null;

            if ($hijo && $hijo->active && $hijo->membershipActive) {
                $activos[] = $id;
            }
        }

        return $activos;
    }

    /**
     * Descendientes unilevel (por patrocinio) que estan activos: OPC y membresia
     * vigentes. Se recorre con pila para no depender de recursion de relaciones.
     *
     * @return array<int>
     */
    private function activeDescendants(int $userId): array
    {
        if (isset($this->descendantsCache[$userId])) {
            return $this->descendantsCache[$userId];
        }

        $encontrados = [];
        $pila = $this->unilevelChildren[$userId] ?? [];
        $visitados = [];

        while ($pila) {
            $id = array_pop($pila);

            if (isset($visitados[$id])) {
                continue;
            }

            $visitados[$id] = true;
            $descendant = $this->usersById[$id] ?? null;

            if ($descendant && $descendant->active && $descendant->membershipActive) {
                $encontrados[] = $id;
            }

            foreach ($this->unilevelChildren[$id] ?? [] as $hijo) {
                $pila[] = $hijo;
            }
        }

        $this->descendantsCache[$userId] = $encontrados;

        return $encontrados;
    }

    /**
     * Bono generacional de un lote ya cortado, para poder lanzarlo por separado.
     *
     * Reconstruye lo que se pago de binario en ese lote a partir de wallet_movements
     * y de rank_binary. Si el lote ya tiene generacional pagado no hace nada.
     */
    public function payGenerationalForBatch(int $batch): float
    {
        $yaPagado = WalletMovements::where('batch', $batch)
            ->where('bonus_type_id', self::BONUS_TYPE_GENERACIONAL)
            ->exists();

        if ($yaPagado) {
            Log::info('[BONO GENERACIONAL] El lote ya tenia generacional pagado, no se repite', [
                'lote' => $batch,
            ]);

            return 0.0;
        }

        $this->usersById = [];
        $this->unilevelChildren = [];
        $this->generacionalPorUsuario = [];
        $this->loadUsers();

        $pagos = DB::table('wallet_movements')
            ->join('wallet', 'wallet.id', '=', 'wallet_movements.wallet_id')
            ->where('wallet_movements.batch', $batch)
            ->where('wallet_movements.bonus_type_id', self::BONUS_TYPE_BINARIO)
            ->select('wallet.user_id', DB::raw('SUM(wallet_movements.amount) as total'))
            ->groupBy('wallet.user_id')
            ->pluck('total', 'user_id')
            ->map(function ($valor) {
                return (float) $valor;
            })
            ->all();

        if (!$pagos) {
            return 0.0;
        }

        $rangos = [];

        foreach (DB::table('rank_binary')->where('batch', $batch)->get(['user_id', 'rank_id']) as $fila) {
            $rangos[(int) $fila->user_id] = RankBonus::find($fila->rank_id);
        }

        return $this->payGenerationalBonuses($pagos, $rangos, $batch);
    }

    /**
     * Bono generacional (Leadership Legacy): un porcentaje de lo que han cobrado de
     * bono binario los patrocinados, generacion a generacion, hasta donde llegue el
     * rango. Se calcula sobre lo realmente pagado en este corte.
     *
     * @param array<int, float> $paidAmounts
     * @param array<int, mixed> $ranksByUser
     */
    private function payGenerationalBonuses(array $paidAmounts, array $ranksByUser, int $batch): float
    {
        if (!$paidAmounts) {
            return 0.0;
        }

        $porcentajes = $this->generationalPercentages();
        $desdeNivelAlto = $this->settings->generacionalNivelAltoDesde();
        $total = 0.0;

        foreach ($paidAmounts as $userId => $_) {
            $rank = $ranksByUser[$userId] ?? null;

            if (!$rank) {
                continue;
            }

            $limite = (int) $rank->limit_generation;

            if ($limite < 1) {
                continue;
            }

            $tabla = $porcentajes[(int) $rank->id] ?? null;

            if ($tabla === null) {
                Log::warning($this->etiqueta . ' Sin porcentajes generacionales para el rango', [
                    'rango_id' => $rank->id,
                    'rango'    => $rank->name,
                ]);
                continue;
            }

            $wallet = Wallet::where('user_id', $userId)->first();

            if (!$wallet) {
                continue;
            }

            $cobrador = $this->usersById[$userId] ?? null;
            $esNivelAlto = $cobrador && $this->membresias->cuentaComoNivelAlto((int) $cobrador->id_account_type);

            $generacion = $this->unilevelChildren[$userId] ?? [];

            for ($nivel = 1; $nivel <= $limite && $generacion; $nivel++) {
                // "A partir de la 3.ª generacion en adelante, es un requisito obligatorio
                // que el IEB cuente con una membresia CROWN o FOUNDERS LEGACY activa."
                $bloqueado = $desdeNivelAlto > 0 && $nivel >= $desdeNivelAlto && !$esNivelAlto;

                $porcentaje = (float) ($tabla[$nivel] ?? 0);

                $base = 0.0;
                foreach ($generacion as $id) {
                    $base += $paidAmounts[$id] ?? 0;
                }

                if (!$bloqueado && $porcentaje > 0 && $base > 0) {
                    $monto = round($base * ($porcentaje / 100), 2);

                    if ($monto > 0) {
                        $movement = new WalletMovements();
                        $movement->wallet_id = $wallet->id;
                        $movement->amount = $monto;
                        $movement->type = 1;
                        $movement->status = 1;
                        $movement->reason = 'Bono de ' . $nivel . '° Generación';
                        $movement->batch = $batch;
                        $movement->bonus_type_id = self::BONUS_TYPE_GENERACIONAL;
                        $movement->save();

                        $total += $monto;
                        $this->generacionalPorUsuario[$userId] = ($this->generacionalPorUsuario[$userId] ?? 0) + $monto;
                    }
                }

                $siguiente = [];
                foreach ($generacion as $id) {
                    foreach ($this->unilevelChildren[$id] ?? [] as $hijo) {
                        $siguiente[] = $hijo;
                    }
                }
                $generacion = $siguiente;
            }
        }

        return $total;
    }

    /**
     * Porcentajes por rango y generacion, desde rank_generation_percentages: una fila
     * por rango y generacion, sin el tope de ocho columnas de generational_bonuses.
     *
     * @return array<int, array<int, float>>
     */
    private function generationalPercentages(): array
    {
        $mapa = [];

        foreach (DB::table('rank_generation_percentages')->get() as $fila) {
            $mapa[(int) $fila->rank_bonus_id][(int) $fila->generation] = (float) $fila->percentage;
        }

        // Un rango sin ninguna fila sigue teniendo tabla (vacia): asi no se confunde
        // "no tiene porcentajes" con "le faltan datos".
        foreach (RankBonus::pluck('id') as $rankId) {
            $mapa[(int) $rankId] = $mapa[(int) $rankId] ?? [];
        }

        return $mapa;
    }
}
