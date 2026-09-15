<?php

namespace App\Services\MLM;

use App\Models\Option;

/**
 * Los parametros del plan que el administrador puede cambiar sin tocar codigo.
 *
 * Viven en la tabla options, que es donde el sistema ya guardaba el lote del corte
 * y la fecha de la ultima entrega del bono de expansion. Aqui solo se les pone
 * nombre, valor por defecto y validacion, para que ninguna de estas decisiones
 * quede escrita a fuego en una clase.
 */
class PlanSettings
{
    public const CORTE_FRECUENCIA = 'binary_cut_frequency';
    public const CORTE_DIA        = 'binary_cut_day';
    public const CORTE_HORA       = 'binary_cut_time';
    public const CORTE_ZONA       = 'binary_cut_timezone';
    public const CORTE_AUTOMATICO = 'binary_cut_automatic';

    /**
     * Generacion a partir de la cual el bono de liderazgo exige una membresia de nivel
     * alto ("CROWN o superior" en el plan nuevo; antes "University"). La clave de la
     * opcion se mantiene para no perder lo ya configurado.
     */
    public const GENERACIONAL_NIVEL_ALTO_DESDE = 'generational_university_from';

    /** Si el bono generacional se paga dentro del corte o como paso aparte. */
    public const GENERACIONAL_EN_EL_CORTE = 'binary_cut_pay_generational_inline';

    /**
     * Cuantas generaciones tiene el plan. Es el numero de columnas de la tabla
     * generacional: el ingeniero quiere poder hacerla crecer desde el panel.
     */
    public const GENERACIONES_MAXIMAS = 'generational_max_generations';

    /**
     * Que se cuenta como "miembros directos activos" y como "membresias de nivel
     * alto" al asignar el rango: 'directos' (solo los patrocinados directos) o 'red'
     * (todos los descendientes activos a cualquier profundidad, que es lo que hace el
     * sistema desde siempre).
     *
     * Se deja en 'red' porque cambiarlo mueve los rangos de todo el mundo y con
     * ellos los topes de cobro: es una decision de negocio, no un arreglo.
     */
    public const RANGO_ALCANCE_DIRECTOS  = 'rank_direct_scope';
    public const RANGO_ALCANCE_NIVEL_ALTO = 'rank_university_scope';

    /**
     * PV que genera cada dolar de un curso comprado. Es la opcion "Puntos/Monto por
     * Compra de Curso" del monolito, con su misma clave para compartir el valor.
     */
    public const PV_POR_USD_CURSO = 'currency_value';

    public const IVA = 'iva_rate';

    public const ALCANCES = ['directos', 'red'];

    private const POR_DEFECTO = [
        self::CORTE_FRECUENCIA => 'monthly',
        self::CORTE_DIA        => '21',
        self::CORTE_HORA       => '12:00',
        self::CORTE_ZONA       => 'America/Lima',

        // El disparo automatico arranca apagado a proposito: encenderlo es una
        // decision que se toma desde el panel.
        self::CORTE_AUTOMATICO => '0',
        self::GENERACIONAL_NIVEL_ALTO_DESDE => '3',
        self::GENERACIONAL_EN_EL_CORTE      => '1',
        self::GENERACIONES_MAXIMAS          => '8',
        self::RANGO_ALCANCE_DIRECTOS        => 'red',
        self::RANGO_ALCANCE_NIVEL_ALTO      => 'red',
        self::PV_POR_USD_CURSO              => '0.29',
        self::IVA                           => '18',
    ];

    public const FRECUENCIAS = ['monthly', 'biweekly'];

    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public function get(string $clave): string
    {
        if (self::$cache === null) {
            self::$cache = Option::pluck('value', 'description')->all();
        }

        $valor = self::$cache[$clave] ?? null;

        if ($valor === null || $valor === '') {
            return self::POR_DEFECTO[$clave] ?? '';
        }

        return (string) $valor;
    }

    public function set(string $clave, string $valor): void
    {
        Option::updateOrCreate(['description' => $clave], ['value' => $valor]);
        self::$cache = null;
    }

    public function frecuenciaCorte(): string
    {
        $valor = $this->get(self::CORTE_FRECUENCIA);

        return in_array($valor, self::FRECUENCIAS, true) ? $valor : 'monthly';
    }

    public function diaCorte(): int
    {
        return max(1, min(28, (int) $this->get(self::CORTE_DIA)));
    }

    public function horaCorte(): string
    {
        $valor = $this->get(self::CORTE_HORA);

        return preg_match('/^\d{1,2}:\d{2}$/', $valor) ? $valor : '12:00';
    }

    public function zonaHoraria(): string
    {
        $valor = $this->get(self::CORTE_ZONA);

        return in_array($valor, timezone_identifiers_list(), true) ? $valor : 'America/Lima';
    }

    public function corteAutomatico(): bool
    {
        return $this->get(self::CORTE_AUTOMATICO) === '1';
    }

    /**
     * A partir de que generacion hace falta nivel alto. 0 desactiva la regla.
     */
    public function generacionalNivelAltoDesde(): int
    {
        return max(0, (int) $this->get(self::GENERACIONAL_NIVEL_ALTO_DESDE));
    }

    public function generacionesMaximas(): int
    {
        return max(1, min(30, (int) $this->get(self::GENERACIONES_MAXIMAS)));
    }

    public function alcanceDirectos(): string
    {
        $valor = $this->get(self::RANGO_ALCANCE_DIRECTOS);

        return in_array($valor, self::ALCANCES, true) ? $valor : 'red';
    }

    public function alcanceNivelAlto(): string
    {
        $valor = $this->get(self::RANGO_ALCANCE_NIVEL_ALTO);

        return in_array($valor, self::ALCANCES, true) ? $valor : 'red';
    }

    public function pvPorUsdCurso(): float
    {
        return max(0.0, (float) $this->get(self::PV_POR_USD_CURSO));
    }

    public function iva(): float
    {
        return max(0.0, (float) $this->get(self::IVA));
    }

    /**
     * Todos los parametros de golpe, para pintarlos en el panel.
     *
     * @return array<string, string|int|float|bool>
     */
    public function todos(): array
    {
        return [
            self::CORTE_FRECUENCIA => $this->frecuenciaCorte(),
            self::CORTE_DIA        => $this->diaCorte(),
            self::CORTE_HORA       => $this->horaCorte(),
            self::CORTE_ZONA       => $this->zonaHoraria(),
            self::CORTE_AUTOMATICO => $this->corteAutomatico(),
            self::GENERACIONAL_NIVEL_ALTO_DESDE => $this->generacionalNivelAltoDesde(),
            self::GENERACIONAL_EN_EL_CORTE      => $this->get(self::GENERACIONAL_EN_EL_CORTE) !== '0',
            self::GENERACIONES_MAXIMAS          => $this->generacionesMaximas(),
            self::RANGO_ALCANCE_DIRECTOS        => $this->alcanceDirectos(),
            self::RANGO_ALCANCE_NIVEL_ALTO      => $this->alcanceNivelAlto(),
            self::PV_POR_USD_CURSO              => $this->pvPorUsdCurso(),
            self::IVA                           => $this->iva(),
        ];
    }

    /** Solo para las pruebas y para despues de escribir: obliga a releer options. */
    public static function olvidarCache(): void
    {
        self::$cache = null;
    }
}
