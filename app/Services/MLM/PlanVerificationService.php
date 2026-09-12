<?php

namespace App\Services\MLM;

use Illuminate\Support\Facades\DB;

/**
 * Contrasta, punto por punto, lo que el sistema tiene configurado con lo que el
 * documento del plan le promete al afiliado.
 *
 * El encargo del equipo fue literal: "el software debe garantizar que esto, cada
 * punto, se da de esta forma". Esto es esa garantia hecha comprobacion. Lo usan el
 * comando "plan:verificar" y el panel de administracion, para que la revision no
 * dependa de que alguien se acuerde de mirar.
 *
 * Lo que se paga sale siempre de la base de datos; el documento vive en
 * config/plan_documento.php. Cuando cambien los nombres, los rangos o los
 * porcentajes, se actualiza ese archivo y se vuelve a pasar esto.
 */
class PlanVerificationService
{
    /** @var array<int, array{seccion: string, concepto: string, documento: string, sistema: string}> */
    private array $diferencias = [];

    /** @var array<int, string> */
    private array $avisos = [];

    /** @var array<int, callable> */
    private array $correcciones = [];

    public function __construct(private PlanSettings $settings)
    {
    }

    /**
     * @param  string|null  $seccion  membresias, opc, corte, rangos o generacional
     *
     * @return array{version: string, diferencias: array, avisos: array<int, string>, total: int}
     */
    public function verificar(?string $seccion = null): array
    {
        $this->diferencias = [];
        $this->avisos = [];
        $this->correcciones = [];

        $plan = config('plan_documento');

        if (!$seccion || $seccion === 'membresias') {
            $this->revisarMembresias($plan);
        }

        if (!$seccion || $seccion === 'opc') {
            $this->revisarOpc($plan);
        }

        if (!$seccion || $seccion === 'corte') {
            $this->revisarCorte($plan);
        }

        if (!$seccion || $seccion === 'rangos') {
            $this->revisarRangos($plan);
        }

        if (!$seccion || $seccion === 'generacional') {
            $this->revisarGeneracional($plan);
        }

        if (!$seccion) {
            $this->revisarBonosQueFaltan($plan);
        }

        return [
            'version'     => $plan['version'],
            'diferencias' => $this->diferencias,
            'avisos'      => $this->avisos,
            'total'       => count($this->diferencias),
        ];
    }

    /**
     * Escribe en la base los valores del documento para las diferencias encontradas
     * en la ultima llamada a verificar().
     *
     * @return int  cuantos valores se han escrito
     */
    public function aplicar(): int
    {
        if (!$this->correcciones) {
            return 0;
        }

        $cuantas = count($this->correcciones);

        DB::transaction(function () {
            foreach ($this->correcciones as $corregir) {
                $corregir();
            }
        });

        PlanSettings::olvidarCache();

        return $cuantas;
    }

    public function correccionesPendientes(): int
    {
        return count($this->correcciones);
    }

    // ------------------------------------------------------------------

    private function revisarMembresias(array $plan): void
    {
        $campos = [
            'price'                 => 'precio sin IGV',
            'fast_cash_bonus'       => 'bono de efectivo rápido (%)',
            'course_selling_bonus'  => 'comisión por venta (%)',
            'disc_purchases_course' => 'descuento en cursos (%)',
            'pay_in_binary'         => 'bono binario (%)',
            'productor_bonus'       => 'bono de propiedad intelectual (%)',
            'enrollment_duration'   => 'vigencia (meses)',
        ];

        foreach ($plan['membresias'] as $nombre => $esperado) {
            $fila = DB::table('account_type')->where('account', $nombre)->first();

            if (!$fila) {
                $this->diferencias[] = [
                    'seccion'   => 'Membresías',
                    'concepto'  => $nombre,
                    'documento' => 'existe',
                    'sistema'   => 'no existe',
                ];
                continue;
            }

            foreach ($campos as $columna => $etiqueta) {
                $this->comparar(
                    'Membresías',
                    "{$nombre} · {$etiqueta}",
                    $esperado[$columna],
                    $fila->{$columna},
                    function () use ($fila, $columna, $esperado) {
                        DB::table('account_type')->where('id', $fila->id)->update([$columna => $esperado[$columna]]);
                    }
                );
            }

            $puntos = DB::table('account_type_points_money')->where('account_type_id', $fila->id)->first();

            $this->comparar(
                'Membresías',
                "{$nombre} · puntos que genera al afiliarse",
                $esperado['puntos_afiliacion'],
                $puntos->points ?? null,
                $puntos
                    ? function () use ($esperado, $puntos) {
                        DB::table('account_type_points_money')
                            ->where('id', $puntos->id)
                            ->update(['points' => $esperado['puntos_afiliacion']]);
                    }
                    : null
            );
        }
    }

    private function revisarOpc(array $plan): void
    {
        $productos = DB::table('product')
            ->leftJoin('account_type', 'account_type.id', '=', 'product.account_type_id')
            ->where('product.name', 'opc')
            ->get(['product.id', 'product.price', 'product.points', 'account_type.account']);

        if ($productos->isEmpty()) {
            $this->avisos[] = 'No hay ningún producto OPC dado de alta.';

            return;
        }

        foreach ($productos as $producto) {
            $etiqueta = $producto->account ?: ('producto ' . $producto->id);

            $this->comparar('OPC', "{$etiqueta} · precio", $plan['opc']['price'], $producto->price,
                function () use ($producto, $plan) {
                    DB::table('product')->where('id', $producto->id)->update(['price' => $plan['opc']['price']]);
                });

            $this->comparar('OPC', "{$etiqueta} · puntos mensuales", $plan['opc']['points'], $producto->points,
                function () use ($producto, $plan) {
                    DB::table('product')->where('id', $producto->id)->update(['points' => $plan['opc']['points']]);
                });
        }

        $conOpc = DB::table('product')->where('name', 'opc')->pluck('account_type_id')->filter()->all();

        $sinOpc = DB::table('account_type')
            ->where('status', '1')
            ->whereNotIn('id', $conOpc ?: [0])
            ->pluck('account');

        if ($sinOpc->isNotEmpty()) {
            $this->avisos[] = 'Membresías activas sin producto OPC, que por tanto no pueden renovar: ' . $sinOpc->implode(', ') . '.';
        }
    }

    private function revisarCorte(array $plan): void
    {
        $this->comparar('Corte binario', 'frecuencia', $plan['corte']['frequency'], $this->settings->frecuenciaCorte(),
            fn () => $this->settings->set(PlanSettings::CORTE_FRECUENCIA, $plan['corte']['frequency']));

        $this->comparar('Corte binario', 'día del mes', $plan['corte']['day'], $this->settings->diaCorte(),
            fn () => $this->settings->set(PlanSettings::CORTE_DIA, (string) $plan['corte']['day']));

        $this->comparar('Corte binario', 'hora', $plan['corte']['time'], $this->settings->horaCorte(),
            fn () => $this->settings->set(PlanSettings::CORTE_HORA, $plan['corte']['time']));

        $this->comparar('Corte binario', 'zona horaria', $plan['corte']['timezone'], $this->settings->zonaHoraria(),
            fn () => $this->settings->set(PlanSettings::CORTE_ZONA, $plan['corte']['timezone']));

        if (!$this->settings->corteAutomatico()) {
            $this->avisos[] = 'El corte automático está apagado: el corte del día ' . $this->settings->diaCorte() . ' no saldrá solo.';
        }

        if ($this->settings->alcanceDirectos() !== 'directos') {
            $this->avisos[] = 'Los «miembros directos activos» del rango se están contando sobre toda la red, '
                . 'no solo sobre los patrocinados directos. El documento dice directos. Se cambia con el ajuste '
                . 'rank_direct_scope, pero mueve el rango y el tope de cobro de todo el mundo: es decisión del equipo.';
        }

        if (!DB::table('binary_cut_runs')->whereNotNull('executed_at')->exists()) {
            $this->avisos[] = 'Todavía no hay ningún corte registrado con el control de periodo nuevo.';
        }
    }

    private function revisarRangos(array $plan): void
    {
        $enBase = DB::table('rank_bonus')->get()->keyBy(fn ($r) => $this->normalizar($r->name));
        $delDocumento = [];

        $campos = [
            'vol_min'                 => 'puntos de la pierna menor',
            'active_direct'           => 'directos activos',
            'pack_max'                => 'membresías University',
            'max_pay'                 => 'tope de cobro',
            'monthly_bonus'           => 'bono mensual de rango',
            'monthly_bonus_months'    => 'meses seguidos para el bono',
            'monthly_bonus_frequency' => 'periodicidad del bono',
            'limit_generation'        => 'generaciones que desbloquea',
        ];

        foreach ($plan['rangos'] as $esperado) {
            $clave = $this->normalizar($esperado['name']);
            $delDocumento[] = $clave;
            $fila = $enBase->get($clave);

            if (!$fila) {
                $this->diferencias[] = [
                    'seccion'   => 'Rangos',
                    'concepto'  => $esperado['name'],
                    'documento' => 'está en el plan',
                    'sistema'   => 'no existe',
                ];
                continue;
            }

            foreach ($campos as $columna => $etiqueta) {
                $this->comparar(
                    'Rangos',
                    "{$esperado['name']} · {$etiqueta}",
                    $esperado[$columna],
                    $fila->{$columna} ?? null,
                    function () use ($fila, $columna, $esperado) {
                        DB::table('rank_bonus')->where('id', $fila->id)->update([$columna => $esperado[$columna]]);
                    }
                );
            }
        }

        $fuera = array_map(fn ($n) => $this->normalizar($n), $plan['rangos_fuera_del_documento']);

        foreach ($enBase as $clave => $fila) {
            if (in_array($clave, $delDocumento, true) || in_array($clave, $fuera, true)) {
                continue;
            }

            $this->avisos[] = "El rango «{$fila->name}» está en el sistema y no aparece en el documento.";
        }

        $conGeneracional = DB::table('generational_bonuses')->whereNotNull('rank_bonus_id')->pluck('rank_bonus_id');

        $sinGeneracional = DB::table('rank_bonus')
            ->where('limit_generation', '>', 0)
            ->whereNotIn('id', $conGeneracional->all() ?: [0])
            ->pluck('name');

        if ($sinGeneracional->isNotEmpty()) {
            $this->avisos[] = 'Rangos que desbloquean generaciones pero no tienen fila de bono generacional, '
                . 'y por tanto no cobrarían nada: ' . $sinGeneracional->implode(', ') . '.';
        }
    }

    private function revisarGeneracional(array $plan): void
    {
        $porcentajes = $plan['generacional']['porcentajes'];

        $filas = DB::table('generational_bonuses')
            ->join('rank_bonus', 'rank_bonus.id', '=', 'generational_bonuses.rank_bonus_id')
            ->get(['generational_bonuses.*', 'rank_bonus.name']);

        $porRango = [];
        foreach ($plan['rangos'] as $rango) {
            $porRango[$this->normalizar($rango['name'])] = $rango['limit_generation'];
        }

        foreach ($filas as $fila) {
            $limite = $porRango[$this->normalizar($fila->name)] ?? null;

            if ($limite === null) {
                continue; // rango que no esta en el documento; ya se avisa aparte
            }

            for ($g = 1; $g <= 8; $g++) {
                // Fuera del alcance del rango el documento no promete nada, asi que
                // ahi lo correcto es cero.
                $esperado = $g <= $limite ? ($porcentajes[$g] ?? 0) : 0;

                $this->comparar(
                    'Bono generacional',
                    "{$fila->name} · {$g}ª generación (%)",
                    $esperado,
                    $fila->{'g_' . $g},
                    function () use ($fila, $g, $esperado) {
                        DB::table('generational_bonuses')->where('id', $fila->id)->update(['g_' . $g => $esperado]);
                    }
                );
            }
        }

        $this->comparar(
            'Bono generacional',
            'generación desde la que se exige University',
            $plan['generacional']['university_desde'],
            $this->settings->generacionalUniversityDesde(),
            fn () => $this->settings->set(
                PlanSettings::GENERACIONAL_UNIVERSITY_DESDE,
                (string) $plan['generacional']['university_desde']
            )
        );
    }

    private function revisarBonosQueFaltan(array $plan): void
    {
        foreach ($plan['bonos_sin_implementar'] as $bono => $detalle) {
            $this->avisos[] = "El documento promete «{$bono}» y el sistema no lo tiene: {$detalle}";
        }

        foreach ($plan['fuera_del_documento'] as $bono => $detalle) {
            $this->avisos[] = "El sistema paga «{$bono}» y el documento no lo menciona: {$detalle}";
        }
    }

    // ------------------------------------------------------------------

    private function comparar(string $seccion, string $concepto, $esperado, $actual, ?callable $corregir = null): void
    {
        if ($this->iguales($esperado, $actual)) {
            return;
        }

        $this->diferencias[] = [
            'seccion'   => $seccion,
            'concepto'  => $concepto,
            'documento' => $this->pintar($esperado),
            'sistema'   => $actual === null ? '—' : $this->pintar($actual),
        ];

        if ($corregir) {
            $this->correcciones[] = $corregir;
        }
    }

    private function iguales($esperado, $actual): bool
    {
        if ($actual === null) {
            return false;
        }

        if (is_numeric($esperado) && is_numeric($actual)) {
            return abs((float) $esperado - (float) $actual) < 0.005;
        }

        return (string) $esperado === (string) $actual;
    }

    private function pintar($valor): string
    {
        return is_numeric($valor)
            ? rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.')
            : (string) $valor;
    }

    private function normalizar(?string $nombre): string
    {
        $limpio = mb_strtolower(trim((string) $nombre));
        $limpio = strtr($limpio, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);

        return preg_replace('/\s+/', '', $limpio) ?? $limpio;
    }
}
