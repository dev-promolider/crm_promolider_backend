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

    /** Generacion a partir de la cual el plan exige membresia University. */
    public const GENERACIONAL_UNIVERSITY_DESDE = 'generational_university_from';

    /** Si el bono generacional se paga dentro del corte o como paso aparte. */
    public const GENERACIONAL_EN_EL_CORTE = 'binary_cut_pay_generational_inline';

    /** Membresia que cuenta como University, por si algun dia cambia de id. */
    public const ID_MEMBRESIA_UNIVERSITY = 'university_account_type_id';

    /**
     * Que se cuenta como "miembros directos activos" y como "membresias University"
     * al asignar el rango: 'directos' (solo los patrocinados directos, que es lo que
     * dice el documento en los rangos bajos) o 'red' (todos los descendientes
     * activos a cualquier profundidad, que es lo que hace el sistema desde siempre).
     *
     * Se deja en 'red' porque cambiarlo mueve los rangos de todo el mundo y con
     * ellos los topes de cobro: es una decision de negocio, no un arreglo.
     */
    public const RANGO_ALCANCE_DIRECTOS   = 'rank_direct_scope';
    public const RANGO_ALCANCE_UNIVERSITY = 'rank_university_scope';

    public const ALCANCES = ['directos', 'red'];

    private const POR_DEFECTO = [
        self::CORTE_FRECUENCIA => 'monthly',
        self::CORTE_DIA        => '21',
        self::CORTE_HORA       => '12:00',
        self::CORTE_ZONA       => 'America/Lima',

        // El calendario queda configurado como manda el documento, pero el disparo
        // automatico arranca APAGADO a proposito. Desde el ultimo corte (29/12/2025)
        // nadie consume volumen, asi que el primer corte que salga pagara sobre todo
        // lo acumulado desde el origen de la red. Encenderlo es una decision que se
        // toma desde el panel, despues de simular ese primer corte sobre una copia y
        // revisar los importes uno a uno. Mientras este apagado, plan:verificar lo
        // avisa en cada pasada para que no se quede olvidado.
        self::CORTE_AUTOMATICO => '0',
        self::GENERACIONAL_UNIVERSITY_DESDE => '3',
        self::ID_MEMBRESIA_UNIVERSITY       => '4',
        self::GENERACIONAL_EN_EL_CORTE      => '1',
        self::RANGO_ALCANCE_DIRECTOS        => 'red',
        self::RANGO_ALCANCE_UNIVERSITY      => 'red',
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
     * A partir de que generacion hace falta University. 0 desactiva la regla.
     */
    public function generacionalUniversityDesde(): int
    {
        return max(0, (int) $this->get(self::GENERACIONAL_UNIVERSITY_DESDE));
    }

    public function idMembresiaUniversity(): int
    {
        return (int) $this->get(self::ID_MEMBRESIA_UNIVERSITY);
    }

    public function alcanceDirectos(): string
    {
        $valor = $this->get(self::RANGO_ALCANCE_DIRECTOS);

        return in_array($valor, self::ALCANCES, true) ? $valor : 'red';
    }

    public function alcanceUniversity(): string
    {
        $valor = $this->get(self::RANGO_ALCANCE_UNIVERSITY);

        return in_array($valor, self::ALCANCES, true) ? $valor : 'red';
    }

    /**
     * Todos los parametros de golpe, para pintarlos en el panel.
     *
     * @return array<string, string|int|bool>
     */
    public function todos(): array
    {
        return [
            self::CORTE_FRECUENCIA => $this->frecuenciaCorte(),
            self::CORTE_DIA        => $this->diaCorte(),
            self::CORTE_HORA       => $this->horaCorte(),
            self::CORTE_ZONA       => $this->zonaHoraria(),
            self::CORTE_AUTOMATICO => $this->corteAutomatico(),
            self::GENERACIONAL_UNIVERSITY_DESDE => $this->generacionalUniversityDesde(),
            self::ID_MEMBRESIA_UNIVERSITY       => $this->idMembresiaUniversity(),
            self::GENERACIONAL_EN_EL_CORTE      => $this->get(self::GENERACIONAL_EN_EL_CORTE) !== '0',
            self::RANGO_ALCANCE_DIRECTOS        => $this->alcanceDirectos(),
            self::RANGO_ALCANCE_UNIVERSITY      => $this->alcanceUniversity(),
        ];
    }

    /** Solo para las pruebas y para despues de escribir: obliga a releer options. */
    public static function olvidarCache(): void
    {
        self::$cache = null;
    }
}
