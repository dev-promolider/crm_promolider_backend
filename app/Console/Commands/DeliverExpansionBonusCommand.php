<?php

namespace App\Console\Commands;

use App\Models\AccountType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bono de expansion: premia haber afiliado directamente a 4 o mas personas con la
 * misma membresia. El porcentaje sube con el numero de afiliados y esta configurado
 * en la tabla expansion_bonus, por membresia y tramo (4-users … 7-users).
 *
 * Diferencia deliberada con el monolito: alli el bono se recalculaba y se volvia a
 * pagar entero en cada entrega mensual sobre los mismos directos. Aqui se guarda el
 * tramo ya cobrado en expansion_bonus_payments y solo se paga al subir de tramo, y
 * solo la diferencia. Es la misma correccion que se aplico al corte binario.
 */
class DeliverExpansionBonusCommand extends Command
{
    protected $signature = 'deliver:expansion-bonus {--dry-run : Calcula y muestra sin pagar nada}';

    protected $description = 'Paga el bono de expansión a los afiliados que subieron de tramo';

    /** Membresias que cuentan para el bono: School, Academy y University. */
    private const MEMBRESIAS = [2, 3, 4];

    private const TRAMO_MINIMO = 4;
    private const TRAMO_MAXIMO = 7;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $precios = AccountType::whereIn('id', self::MEMBRESIAS)->pluck('price', 'id');
        $porcentajes = $this->loadPercentages();
        $pagados = DB::table('expansion_bonus_payments')->get()
            ->keyBy(fn ($fila) => $fila->user_id . ':' . $fila->account_type_id);

        $totalPagado = 0.0;
        $beneficiarios = 0;

        $candidatos = User::query()
            ->whereIn('id', DB::table('users')->whereIn('id_account_type', self::MEMBRESIAS)
                ->where('request', 2)->distinct()->pluck('id_referrer_sponsor'))
            ->get();

        foreach ($candidatos as $user) {
            if (!$user->active || !$user->membershipActive || !$user->qualified) {
                continue;
            }

            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                continue;
            }

            foreach (self::MEMBRESIAS as $accountTypeId) {
                $directos = User::where('id_referrer_sponsor', $user->id)
                    ->where('id_account_type', $accountTypeId)
                    ->where('request', 2)
                    ->count();

                if ($directos < self::TRAMO_MINIMO) {
                    continue;
                }

                $tramo = min($directos, self::TRAMO_MAXIMO);
                $clave = $user->id . ':' . $accountTypeId;
                $previo = $pagados->get($clave);
                $tramoPrevio = $previo ? (int) $previo->tier : 0;

                if ($tramo <= $tramoPrevio) {
                    continue;
                }

                $porcentaje = (float) ($porcentajes[$accountTypeId][$tramo] ?? 0);
                $precio = (float) ($precios[$accountTypeId] ?? 0);
                $importeTramo = round($precio * ($porcentaje / 100) * $tramo, 2);
                $yaCobrado = $previo ? (float) $previo->paid_amount : 0.0;
                $aPagar = round($importeTramo - $yaCobrado, 2);

                if ($aPagar <= 0) {
                    continue;
                }

                $nombre = AccountType::find($accountTypeId)->account ?? ('Tipo ' . $accountTypeId);
                $this->line(sprintf(
                    '  usuario %d · %s · %d directos · tramo %d → $%s',
                    $user->id, $nombre, $directos, $tramo, number_format($aPagar, 2)
                ));

                if ($dryRun) {
                    continue;
                }

                DB::transaction(function () use ($wallet, $aPagar, $user, $accountTypeId, $tramo, $importeTramo, $nombre, $directos) {
                    $movement = new WalletMovements();
                    $movement->wallet_id = $wallet->id;
                    $movement->amount = $aPagar;
                    $movement->type = 1;
                    $movement->status = 1;
                    $movement->reason = 'Bono de expansión · ' . $nombre . ' · ' . $directos . ' directos';
                    $movement->batch = 0;
                    $movement->bonus_type_id = 6;
                    $movement->save();

                    DB::table('expansion_bonus_payments')->updateOrInsert(
                        ['user_id' => $user->id, 'account_type_id' => $accountTypeId],
                        [
                            'tier'        => $tramo,
                            'paid_amount' => $importeTramo,
                            'updated_at'  => now(),
                            'created_at'  => now(),
                        ]
                    );
                });

                $totalPagado += $aPagar;
                $beneficiarios++;
            }
        }

        $resumen = sprintf(
            '%sBono de expansión: %d pagos, $%s en total.',
            $dryRun ? '[simulación] ' : '',
            $beneficiarios,
            number_format($totalPagado, 2)
        );

        $this->info($resumen);
        Log::info('[BONO EXPANSION] ' . $resumen);

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<int, float>> [account_type_id][tramo] = porcentaje
     */
    private function loadPercentages(): array
    {
        $mapa = [];

        foreach (DB::table('expansion_bonus')->get() as $fila) {
            if (!preg_match('/^(\d+)-users$/', $fila->name, $m)) {
                continue;
            }

            $mapa[(int) $fila->id_account_type][(int) $m[1]] = (float) $fila->value;
        }

        return $mapa;
    }
}
