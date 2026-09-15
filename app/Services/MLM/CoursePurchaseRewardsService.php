<?php

namespace App\Services\MLM;

use App\Models\Course;
use App\Models\Option;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lo que genera la compra de un curso.
 *
 * En el sistema nuevo comprar un curso solo registraba la compra: ni el creador cobraba
 * su parte, ni quien lo recomendo su comision, ni se generaban PV. Los ultimos pagos de
 * este tipo en produccion son de abril de 2026, cuando las compras aun pasaban por el
 * monolito. Aqui se recupera lo que hacia el monolito, con los nombres del plan nuevo:
 *
 *   - Smart Savings: descuento para el comprador segun su membresia vigente.
 *   - Creator Royalty: el creador del curso cobra el % de "propiedad intelectual" de su
 *     membresia sobre el precio del curso.
 *   - Marketplace Profit: el patrocinador del comprador cobra el % de "venta de cursos"
 *     de su membresia, si la tiene vigente.
 *   - PV: lo pagado por el curso, multiplicado por "PV por cada dolar de curso" (la
 *     opcion 0.29 del monolito; el plan nuevo dice 1 PV = 1 USD), sube por el arbol.
 *
 * Cada compra reparte una sola vez aunque la confirmacion llegue dos veces.
 */
class CoursePurchaseRewardsService
{
    private const BONUS_VENTA = 2;
    private const BONUS_CREADOR = 3;

    public function __construct(private PlanSettings $settings)
    {
    }

    /**
     * Precio que paga un comprador concreto por un curso.
     *
     * @return array{precio: float, descuento: float, final: float}
     */
    public function precioParaComprador(Course $curso, ?User $comprador): array
    {
        $precio = (float) ($curso->price > 0 ? $curso->price : $curso->price_base);
        $descuento = 0.0;

        if ($comprador && $comprador->membershipActive) {
            $descuento = (float) ($comprador->accountType->disc_purchases_course ?? 0);
        }

        $descuento = max(0.0, min(100.0, $descuento));

        return [
            'precio'    => round($precio, 2),
            'descuento' => $descuento,
            'final'     => round($precio * (1 - $descuento / 100), 2),
        ];
    }

    /**
     * @return array{repartido: bool, creador: float, venta: float, pv: float, filas_pv: int}
     */
    public function repartir(int $compradorId, int $cursoId, float $importePagado): array
    {
        $vacio = ['repartido' => false, 'creador' => 0.0, 'venta' => 0.0, 'pv' => 0.0, 'filas_pv' => 0];

        $curso = Course::find($cursoId);
        $comprador = User::find($compradorId);

        if (!$curso || !$comprador) {
            Log::warning('[COMPRA DE CURSO] Curso o comprador no encontrado', ['curso' => $cursoId, 'comprador' => $compradorId]);
            return $vacio;
        }

        return DB::transaction(function () use ($curso, $comprador, $importePagado, $vacio) {
            // La marca se pone primero y de forma condicional: si otra confirmacion ya
            // repartio esta compra, no se actualiza ninguna fila y aqui se para.
            $marcadas = DB::table('purchased_courses')
                ->where('user_id', $comprador->id)
                ->where('course_id', $curso->id)
                ->whereNull('rewards_distributed_at')
                ->update([
                    'amount_paid'            => $importePagado,
                    'rewards_distributed_at' => now(),
                    'updated_at'             => now(),
                ]);

            if ($marcadas === 0) {
                Log::info('[COMPRA DE CURSO] Premios ya repartidos o compra no registrada', [
                    'curso'     => $curso->id,
                    'comprador' => $comprador->id,
                ]);
                return $vacio;
            }

            $precio = (float) ($curso->price > 0 ? $curso->price : $curso->price_base);
            $lote = (int) (Option::where('description', 'batch')->value('value') ?? 1);
            $creado = 0.0;
            $venta = 0.0;

            $creador = $curso->user_id ? User::find($curso->user_id) : null;

            if ($creador && (int) $creador->id !== (int) $comprador->id) {
                $porcentaje = (float) ($creador->accountType->productor_bonus ?? 0);
                $creado = $this->pagar(
                    $creador,
                    round($precio * $porcentaje / 100, 2),
                    self::BONUS_CREADOR,
                    'Bono de productor por venta de «' . mb_substr((string) $curso->title, 0, 120) . '» a ' . $comprador->username,
                    $lote,
                    $comprador->id
                );
            }

            $patrocinador = $comprador->id_referrer_sponsor ? User::find($comprador->id_referrer_sponsor) : null;

            if ($patrocinador && (int) $patrocinador->id > 1 && $patrocinador->membershipActive) {
                $porcentaje = (float) ($patrocinador->accountType->course_selling_bonus ?? 0);
                $venta = $this->pagar(
                    $patrocinador,
                    round($precio * $porcentaje / 100, 2),
                    self::BONUS_VENTA,
                    'Bono por compra de curso de ' . $comprador->username,
                    $lote,
                    $comprador->id
                );
            }

            $pv = round($importePagado * $this->settings->pvPorUsdCurso());
            $filas = $pv > 0 ? app(AffiliationRewardsService::class)->distributeCoursePoints($comprador->id, $pv) : 0;

            $resumen = [
                'repartido' => true,
                'creador'   => $creado,
                'venta'     => $venta,
                'pv'        => (float) $pv,
                'filas_pv'  => $filas,
            ];

            Log::info('[COMPRA DE CURSO] Premios repartidos', $resumen + ['curso' => $curso->id, 'comprador' => $comprador->id]);

            return $resumen;
        });
    }

    private function pagar(User $beneficiario, float $monto, int $tipo, string $motivo, int $lote, int $compradorId): float
    {
        if ($monto <= 0) {
            return 0.0;
        }

        $wallet = Wallet::where('user_id', $beneficiario->id)->first();

        if (!$wallet) {
            Log::warning('[COMPRA DE CURSO] Beneficiario sin billetera', ['user_id' => $beneficiario->id]);
            return 0.0;
        }

        $movimiento = new WalletMovements();
        $movimiento->wallet_id = $wallet->id;
        $movimiento->amount = $monto;
        $movimiento->type = 1;
        $movimiento->status = 1;
        $movimiento->batch = $lote;
        $movimiento->bonus_type_id = $tipo;
        $movimiento->reason = mb_substr($motivo, 0, 255);
        $movimiento->user_purchase_id = $compradorId;
        $movimiento->save();

        return $monto;
    }
}
