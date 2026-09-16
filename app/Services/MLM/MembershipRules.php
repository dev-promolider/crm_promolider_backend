<?php

namespace App\Services\MLM;

use App\Models\AccountType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Lo que cada membresia decide sobre el plan, leido de su configuracion.
 *
 * Antes estas reglas estaban escritas con identificadores fijos repartidos por el
 * codigo: el 4 era "University" para rangos y generacional, el 5 y el 6 no
 * alimentaban la red, y todas las membresias pedian OPC y duraban 365 dias. Con el
 * plan nuevo las membresias se crean desde el panel, asi que ninguna de esas
 * decisiones puede depender de un numero.
 *
 * Si una membresia no existe (datos viejos o borrados), cada regla devuelve lo que
 * hacia el sistema antes, para no cambiar la condicion de nadie por un hueco.
 */
class MembershipRules
{
    /** Fecha que ya usa la base para "sin vencimiento". */
    public const SIN_VENCIMIENTO = '9999-12-31 23:59:59';

    /** @var array<int, object>|null */
    private static ?array $cache = null;

    /**
     * Genera PV al afiliarse y cuenta como directo activo para calificar.
     */
    public function alimentaLaRed(?int $accountTypeId): bool
    {
        $membresia = $this->membresia($accountTypeId);

        return $membresia ? (bool) $membresia->feeds_network : true;
    }

    /**
     * Cuenta como "CROWN o superior": requisito de algunos rangos y de las
     * generaciones altas del bono de liderazgo.
     */
    public function cuentaComoNivelAlto(?int $accountTypeId): bool
    {
        $membresia = $this->membresia($accountTypeId);

        return $membresia ? (bool) $membresia->counts_as_top_tier : false;
    }

    /**
     * Si para estar activo hace falta tener el OPC al dia.
     */
    public function requiereOpc(?int $accountTypeId): bool
    {
        $membresia = $this->membresia($accountTypeId);

        return $membresia ? (bool) $membresia->requires_opc : true;
    }

    public function esPermanente(?int $accountTypeId): bool
    {
        $membresia = $this->membresia($accountTypeId);

        return $membresia ? (bool) $membresia->is_permanent : false;
    }

    public function mesesDeVigencia(?int $accountTypeId): int
    {
        $membresia = $this->membresia($accountTypeId);
        $meses = $membresia ? (int) $membresia->enrollment_duration : 12;

        return $meses > 0 ? $meses : 12;
    }

    /**
     * Fecha de fin de la membresia contratada hoy (o desde la fecha dada).
     */
    public function vencimientoMembresia(?int $accountTypeId, ?Carbon $desde = null): Carbon
    {
        if ($this->esPermanente($accountTypeId)) {
            return Carbon::parse(self::SIN_VENCIMIENTO);
        }

        return ($desde ? $desde->copy() : now())->addMonthsNoOverflow($this->mesesDeVigencia($accountTypeId));
    }

    /**
     * Distintivo que se ensena junto al nombre del afiliado, si la membresia lo tiene.
     *
     * @return array{label: string, color: string|null}|null
     */
    public function distintivo(?int $accountTypeId): ?array
    {
        $membresia = $this->membresia($accountTypeId);

        if (!$membresia || !$membresia->highlight_label) {
            return null;
        }

        return [
            'label' => (string) $membresia->highlight_label,
            'color' => $membresia->highlight_color ?: null,
        ];
    }

    /**
     * @return array<int>
     */
    public function idsNivelAlto(): array
    {
        return array_keys(array_filter($this->todas(), fn ($m) => (bool) $m->counts_as_top_tier));
    }

    /**
     * Hay que llamarlo despues de tocar una membresia desde el panel. Tambien vacia
     * la cache de "tipos de pago" del modelo User, que se guardaba un dia entero:
     * sin esto, una membresia recien creada no contaria como de pago hasta manana.
     */
    public static function olvidar(): void
    {
        self::$cache = null;
        Cache::forget('valid_account_types');
    }

    private function membresia(?int $accountTypeId): ?object
    {
        if (!$accountTypeId) {
            return null;
        }

        return $this->todas()[(int) $accountTypeId] ?? null;
    }

    /**
     * @return array<int, object>
     */
    private function todas(): array
    {
        if (self::$cache === null) {
            self::$cache = AccountType::query()
                ->get([
                    'id', 'account', 'enrollment_duration', 'is_permanent', 'requires_opc',
                    'feeds_network', 'counts_as_top_tier', 'highlight_label', 'highlight_color',
                ])
                ->keyBy('id')
                ->all();
        }

        return self::$cache;
    }
}
