<?php

namespace App\Services\MLM;

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
 * «Puntos Binarios Activos» y en el arbol. Antes el corte ignoraba esa tabla y
 * recalculaba el volumen recorriendo el arbol entero, con dos consecuencias: lo que
 * se pagaba no tenia relacion con lo que se mostraba, y como el recalculo era
 * acumulativo desde el origen, cada corte volvia a pagar sobre el mismo volumen.
 *
 * Ahora el volumen se consume: los puntos que entran en el corte quedan en status 0
 * y el sobrante de la pierna mayor se guarda como una unica fila de arrastre.
 */
class BinaryCutService
{
    /** Porcentaje de la pierna menor que se paga, por generacion, si no hay tabla. */
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

    /**
     * @return array{lote: int, pagados: int, total_binario: float, total_generacional: float}
     */
    public function execute(): array
    {
        // El servicio guarda mapas en memoria para no repetir consultas. Si se le llama
        // dos veces (dos cortes seguidos), hay que partir de cero: si no, el segundo
        // corte trabajaria con el volumen del primero y volveria a pagarlo.
        $this->volumes = [];
        $this->unilevelChildren = [];
        $this->usersById = [];
        $this->descendantsCache = [];

        $batchOption = Option::firstOrCreate(['description' => 'batch'], ['value' => '1']);
        $batch = (int) $batchOption->value;

        Log::info('[CORTE BINARIO] Iniciando', ['lote' => $batch]);

        $ranks = RankBonus::orderBy('vol_min')->orderBy('id')->get();

        if ($ranks->isEmpty()) {
            throw new \RuntimeException('No hay rangos configurados en rank_bonus.');
        }

        $this->loadUsers();
        $this->loadVolumes();

        $paidAmounts = [];
        $ranksByUser = [];
        $totalBinario = 0.0;

        foreach ($this->volumes as $userId => $volume) {
            $user = $this->usersById[$userId] ?? null;

            if (!$user) {
                continue;
            }

            if (!$user->active || !$user->membershipActive || !$user->qualified) {
                continue;
            }

            $left = (float) $volume['left'];
            $right = (float) $volume['right'];
            $min = min($left, $right);
            $max = max($left, $right);

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
                Log::warning('[CORTE BINARIO] Usuario sin billetera, se omite', ['user_id' => $userId]);
                continue;
            }

            $payInBinary = (float) ($user->accountType->pay_in_binary ?? 0);
            $amount = round($min * ($payInBinary / 100), 2);

            if ($amount > (float) $rank->max_pay) {
                Log::info('[CORTE BINARIO] Bono topado por rango', [
                    'user_id'   => $userId,
                    'calculado' => $amount,
                    'tope'      => $rank->max_pay,
                ]);
                $amount = (float) $rank->max_pay;
            }

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
                'left_points'        => $left,
                'right_points'       => $right,
                'transferred_amount' => $amount,
                'batch'              => $batch,
            ]);
        }

        $totalGeneracional = $this->payGenerationalBonuses($paidAmounts, $ranksByUser, $batch);

        $batchOption->value = (string) ($batch + 1);
        $batchOption->save();

        $resumen = [
            'lote'               => $batch,
            'pagados'            => count($paidAmounts),
            'total_binario'      => round($totalBinario, 2),
            'total_generacional' => round($totalGeneracional, 2),
        ];

        Log::info('[CORTE BINARIO] Terminado', $resumen);

        return $resumen;
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
     * Rango del corte. Ademas del volumen de la pierna menor exige descendientes
     * activos y, en los rangos que lo piden, un numero de membresias University.
     */
    private function resolveRank(User $user, float $minPoints, $ranks)
    {
        $descendants = $this->activeDescendants((int) $user->id);
        $activeCount = count($descendants);

        $universityCount = 0;
        foreach ($descendants as $descendantId) {
            $descendant = $this->usersById[$descendantId] ?? null;
            if ($descendant && (int) $descendant->id_account_type === 4) {
                $universityCount++;
            }
        }

        $elegido = $ranks->first();

        foreach ($ranks as $rank) {
            $cumple = $minPoints >= (float) $rank->vol_min
                && $activeCount >= (int) $rank->active_direct
                && $universityCount >= (int) $rank->pack_max;

            if ($cumple) {
                $elegido = $rank;
            }
        }

        return $elegido;
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
     * Bono generacional (matching): un porcentaje de lo que han cobrado de bono binario
     * los patrocinados, generacion a generacion, hasta donde llegue el rango.
     *
     * Se calcula sobre lo realmente pagado en este corte, no sobre lo que a cada uno
     * le habria correspondido: es lo que describe el plan al afiliado y evita pagar
     * matching sobre dinero que nadie llego a cobrar.
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

            $tabla = $porcentajes[$rank->name] ?? null;

            if ($tabla === null) {
                Log::warning('[CORTE BINARIO] Sin porcentajes generacionales para el rango', [
                    'rango' => $rank->name,
                ]);
                continue;
            }

            $wallet = Wallet::where('user_id', $userId)->first();

            if (!$wallet) {
                continue;
            }

            $generacion = $this->unilevelChildren[$userId] ?? [];

            for ($nivel = 1; $nivel <= $limite && $generacion; $nivel++) {
                $porcentaje = (float) ($tabla[$nivel] ?? 0);

                $base = 0.0;
                foreach ($generacion as $id) {
                    $base += $paidAmounts[$id] ?? 0;
                }

                if ($porcentaje > 0 && $base > 0) {
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
     * Porcentajes por rango y generacion, desde generational_bonuses.
     * Se cruza por nombre de rango: los identificadores de las dos tablas no coinciden.
     *
     * @return array<string, array<int, float>>
     */
    private function generationalPercentages(): array
    {
        $filas = DB::table('generational_bonuses')->get();
        $mapa = [];

        foreach ($filas as $fila) {
            $porGeneracion = [];

            for ($i = 1; $i <= 8; $i++) {
                $porGeneracion[$i] = (float) ($fila->{'g_' . $i} ?? 0);
            }

            $mapa[$fila->range_name] = $porGeneracion;
        }

        return $mapa;
    }
}
