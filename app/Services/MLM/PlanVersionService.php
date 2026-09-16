<?php

namespace App\Services\MLM;

use App\Models\AccountType;
use App\Models\RankBonus;
use Illuminate\Support\Facades\DB;

/**
 * Versiones guardadas del plan de compensacion.
 *
 * Sustituyen al contraste con el documento. Aquel comparaba contra un archivo del
 * codigo que el administrador no podia tocar, y el ingeniero lo dijo claro: cuando
 * cambie el plan no hay donde cambiarlo. Ahora la configuracion del panel es el plan;
 * una version es una foto completa de esa configuracion, para comparar con lo que hay
 * y volver atras si un cambio sale mal.
 */
class PlanVersionService
{
    private const CAMPOS_MEMBRESIA = [
        'account', 'category_id', 'description', 'sort_order', 'price', 'iva',
        'fast_cash_bonus', 'pay_in_binary', 'course_selling_bonus', 'disc_purchases_course',
        'disc_purchases_certificates', 'productor_bonus', 'enrollment_duration', 'status',
        'is_visible', 'is_permanent', 'requires_opc', 'feeds_network', 'counts_as_top_tier',
        'max_members', 'highlight_label', 'highlight_color',
    ];

    private const CAMPOS_RANGO = [
        'name', 'sort_order', 'vol_min', 'active_direct', 'pack_max',
        'min_months_previous_rank', 'max_pay',
        'monthly_bonus', 'monthly_bonus_months', 'monthly_bonus_frequency',
        'limit_generation', 'icon', 'status',
    ];

    public function __construct(private PlanSettings $settings)
    {
    }

    /**
     * La configuracion completa del plan en este momento.
     */
    public function fotografia(): array
    {
        return [
            'formato'      => 1,
            'tomada_el'    => now()->toDateTimeString(),
            'membresias'   => AccountType::orderBy('sort_order')->orderBy('id')->get()
                ->map(fn ($m) => ['id' => (int) $m->id] + $m->only(self::CAMPOS_MEMBRESIA))
                ->all(),
            'opc'          => DB::table('product')->where('name', 'opc')->whereNotNull('account_type_id')
                ->get(['account_type_id', 'price', 'points', 'status'])
                ->map(fn ($p) => (array) $p)->all(),
            'puntos'       => DB::table('account_type_points_money')
                ->get(['account_type_id', 'points'])
                ->map(fn ($p) => (array) $p)->all(),
            'categorias'   => DB::table('membership_categories')->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'description', 'sort_order', 'status'])
                ->map(fn ($c) => (array) $c)->all(),
            'rangos'       => RankBonus::orderBy('sort_order')->orderBy('id')->get()
                ->map(fn ($r) => ['id' => (int) $r->id] + $r->only(self::CAMPOS_RANGO))
                ->all(),
            'generaciones' => DB::table('rank_generation_percentages')
                ->orderBy('rank_bonus_id')->orderBy('generation')
                ->get(['rank_bonus_id', 'generation', 'percentage'])
                ->map(fn ($g) => (array) $g)->all(),
            'ajustes'      => $this->settings->todos(),
        ];
    }

    public function guardar(string $nombre, ?string $notas, ?int $usuario, bool $automatica = false): int
    {
        return DB::table('compensation_plan_versions')->insertGetId([
            'name'         => mb_substr($nombre, 0, 150),
            'notes'        => $notas,
            'snapshot'     => json_encode($this->fotografia(), JSON_UNESCAPED_UNICODE),
            'created_by'   => $usuario,
            'is_automatic' => $automatica,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    /**
     * Diferencias entre una version guardada y la configuracion actual.
     *
     * @return array<int, array{apartado: string, concepto: string, version: string, actual: string}>
     */
    public function comparar(int $versionId): array
    {
        $version = $this->leer($versionId);
        $actual = $this->fotografia();
        $diferencias = [];

        $this->compararColeccion(
            $diferencias, 'Membresías', $version['membresias'], $actual['membresias'],
            'id', self::CAMPOS_MEMBRESIA, fn ($m) => $m['account'] ?? ('#' . $m['id'])
        );

        $this->compararColeccion(
            $diferencias, 'OPC', $version['opc'], $actual['opc'],
            'account_type_id', ['price', 'points', 'status'],
            fn ($o) => 'OPC de ' . $this->nombreMembresia($version, $actual, $o['account_type_id'])
        );

        $this->compararColeccion(
            $diferencias, 'PV de afiliación', $version['puntos'], $actual['puntos'],
            'account_type_id', ['points'],
            fn ($p) => $this->nombreMembresia($version, $actual, $p['account_type_id'])
        );

        $this->compararColeccion(
            $diferencias, 'Rangos', $version['rangos'], $actual['rangos'],
            'id', self::CAMPOS_RANGO, fn ($r) => $r['name'] ?? ('#' . $r['id'])
        );

        $claveGen = fn ($g) => $g['rank_bonus_id'] . ':' . $g['generation'];
        $genVersion = [];
        foreach ($version['generaciones'] as $g) {
            $genVersion[$claveGen($g)] = $g;
        }
        $genActual = [];
        foreach ($actual['generaciones'] as $g) {
            $genActual[$claveGen($g)] = $g;
        }

        foreach (array_unique(array_merge(array_keys($genVersion), array_keys($genActual))) as $clave) {
            $antes = (float) ($genVersion[$clave]['percentage'] ?? 0);
            $ahora = (float) ($genActual[$clave]['percentage'] ?? 0);

            if (abs($antes - $ahora) >= 0.005) {
                [$rangoId, $generacion] = explode(':', $clave);
                $diferencias[] = [
                    'apartado' => 'Bono generacional',
                    'concepto' => $this->nombreRango($version, $actual, (int) $rangoId) . ' · ' . $generacion . 'ª generación (%)',
                    'version'  => $this->pintar($antes),
                    'actual'   => $this->pintar($ahora),
                ];
            }
        }

        foreach ($version['ajustes'] as $clave => $valor) {
            $ahora = $actual['ajustes'][$clave] ?? null;

            if ($this->pintar($valor) !== $this->pintar($ahora)) {
                $diferencias[] = [
                    'apartado' => 'Ajustes',
                    'concepto' => $clave,
                    'version'  => $this->pintar($valor),
                    'actual'   => $this->pintar($ahora),
                ];
            }
        }

        return $diferencias;
    }

    /**
     * Devuelve la configuracion a como estaba en una version.
     *
     * Antes de tocar nada guarda una version automatica con lo que hay, asi que
     * restaurar tambien se puede deshacer. Solo se actualiza lo que sigue existiendo:
     * lo borrado despues no se recrea y lo creado despues no se borra; las dos cosas
     * salen en el informe.
     *
     * @return array{respaldo: int, actualizados: int, omitidos: array<int, string>, creados_despues: array<int, string>}
     */
    public function restaurar(int $versionId, ?int $usuario): array
    {
        $registro = DB::table('compensation_plan_versions')->find($versionId);
        $version = $this->leer($versionId);

        return DB::transaction(function () use ($registro, $version, $usuario) {
            $respaldo = $this->guardar('Antes de restaurar «' . $registro->name . '»', null, $usuario, true);
            $actualizados = 0;
            $omitidos = [];

            foreach ($version['membresias'] as $m) {
                $datos = array_intersect_key($m, array_flip(self::CAMPOS_MEMBRESIA));

                if (!AccountType::where('id', $m['id'])->exists()) {
                    $omitidos[] = 'Membresía «' . ($m['account'] ?? $m['id']) . '» (ya no existe)';
                    continue;
                }

                AccountType::where('id', $m['id'])->update($datos);
                $actualizados++;
            }

            foreach ($version['opc'] as $o) {
                if (!AccountType::where('id', $o['account_type_id'])->exists()) {
                    continue;
                }

                DB::table('product')->updateOrInsert(
                    ['name' => 'opc', 'account_type_id' => $o['account_type_id']],
                    [
                        'price'            => $o['price'],
                        'points'           => $o['points'],
                        'status'           => $o['status'],
                        'descripcion'      => 'OPC',
                        'promotion_prince' => 0,
                        'commission'       => 0,
                        'updated_at'       => now(),
                    ]
                );
            }

            foreach ($version['puntos'] as $p) {
                if (!AccountType::where('id', $p['account_type_id'])->exists()) {
                    continue;
                }

                DB::table('account_type_points_money')->updateOrInsert(
                    ['account_type_id' => $p['account_type_id']],
                    ['points' => $p['points'], 'updated_at' => now()]
                );
            }

            $rangosVivos = [];

            foreach ($version['rangos'] as $r) {
                if (!RankBonus::where('id', $r['id'])->exists()) {
                    $omitidos[] = 'Rango «' . ($r['name'] ?? $r['id']) . '» (ya no existe)';
                    continue;
                }

                RankBonus::where('id', $r['id'])->update(array_intersect_key($r, array_flip(self::CAMPOS_RANGO)));
                $rangosVivos[] = (int) $r['id'];
                $actualizados++;
            }

            DB::table('rank_generation_percentages')->whereIn('rank_bonus_id', $rangosVivos)->delete();

            foreach ($version['generaciones'] as $g) {
                if (!in_array((int) $g['rank_bonus_id'], $rangosVivos, true)) {
                    continue;
                }

                DB::table('rank_generation_percentages')->insert([
                    'rank_bonus_id' => $g['rank_bonus_id'],
                    'generation'    => $g['generation'],
                    'percentage'    => $g['percentage'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            foreach ($rangosVivos as $rangoId) {
                $this->sincronizarLegacy($rangoId);
            }

            foreach ($version['ajustes'] as $clave => $valor) {
                $this->settings->set($clave, is_bool($valor) ? ($valor ? '1' : '0') : (string) $valor);
            }

            $idsMembresias = array_column($version['membresias'], 'id');
            $idsRangos = array_column($version['rangos'], 'id');

            $creadosDespues = array_merge(
                AccountType::whereNotIn('id', $idsMembresias ?: [0])->pluck('account')
                    ->map(fn ($n) => 'Membresía «' . $n . '»')->all(),
                RankBonus::whereNotIn('id', $idsRangos ?: [0])->pluck('name')
                    ->map(fn ($n) => 'Rango «' . $n . '»')->all()
            );

            MembershipRules::olvidar();
            PlanSettings::olvidarCache();

            return [
                'respaldo'        => $respaldo,
                'actualizados'    => $actualizados,
                'omitidos'        => $omitidos,
                'creados_despues' => $creadosDespues,
            ];
        });
    }

    private function leer(int $versionId): array
    {
        $registro = DB::table('compensation_plan_versions')->find($versionId);

        if (!$registro) {
            throw new \RuntimeException('Versión no encontrada.', 404);
        }

        $datos = json_decode($registro->snapshot, true);

        if (!is_array($datos)) {
            throw new \RuntimeException('La versión guardada está dañada.', 422);
        }

        foreach (['membresias', 'opc', 'puntos', 'categorias', 'rangos', 'generaciones', 'ajustes'] as $clave) {
            $datos[$clave] = $datos[$clave] ?? [];
        }

        return $datos;
    }

    private function compararColeccion(array &$diferencias, string $apartado, array $antes, array $ahora, string $clave, array $campos, callable $nombre): void
    {
        $porClaveAntes = [];
        foreach ($antes as $fila) {
            $porClaveAntes[$fila[$clave]] = $fila;
        }
        $porClaveAhora = [];
        foreach ($ahora as $fila) {
            $porClaveAhora[$fila[$clave]] = $fila;
        }

        foreach ($porClaveAntes as $id => $fila) {
            if (!isset($porClaveAhora[$id])) {
                $diferencias[] = [
                    'apartado' => $apartado,
                    'concepto' => $nombre($fila),
                    'version'  => 'existía',
                    'actual'   => 'ya no existe',
                ];
                continue;
            }

            foreach ($campos as $campo) {
                // Un campo que no existia cuando se guardo la version no es una
                // diferencia, es una columna nueva: compararlo sacaria una fila por
                // cada rango cada vez que el plan gana un campo.
                if (!array_key_exists($campo, $fila)) {
                    continue;
                }

                $a = $fila[$campo] ?? null;
                $b = $porClaveAhora[$id][$campo] ?? null;

                if ($this->pintar($a) !== $this->pintar($b)) {
                    $diferencias[] = [
                        'apartado' => $apartado,
                        'concepto' => $nombre($fila) . ' · ' . $campo,
                        'version'  => $this->pintar($a),
                        'actual'   => $this->pintar($b),
                    ];
                }
            }
        }

        foreach ($porClaveAhora as $id => $fila) {
            if (!isset($porClaveAntes[$id])) {
                $diferencias[] = [
                    'apartado' => $apartado,
                    'concepto' => $nombre($fila),
                    'version'  => 'no existía',
                    'actual'   => 'creado después',
                ];
            }
        }
    }

    private function nombreMembresia(array $version, array $actual, $id): string
    {
        foreach (array_merge($actual['membresias'], $version['membresias']) as $m) {
            if ((int) $m['id'] === (int) $id) {
                return (string) $m['account'];
            }
        }

        return '#' . $id;
    }

    private function nombreRango(array $version, array $actual, int $id): string
    {
        foreach (array_merge($actual['rangos'], $version['rangos']) as $r) {
            if ((int) $r['id'] === $id) {
                return (string) $r['name'];
            }
        }

        return '#' . $id;
    }

    private function pintar($valor): string
    {
        if ($valor === null) {
            return '—';
        }

        if (is_bool($valor)) {
            return $valor ? 'sí' : 'no';
        }

        if (is_numeric($valor)) {
            return rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');
        }

        return (string) $valor;
    }

    private function sincronizarLegacy(int $rangoId): void
    {
        $rango = RankBonus::find($rangoId);

        if (!$rango) {
            return;
        }

        $mapa = DB::table('rank_generation_percentages')
            ->where('rank_bonus_id', $rangoId)
            ->pluck('percentage', 'generation')
            ->all();

        $datos = ['range_name' => $rango->name, 'updated_at' => now()];
        for ($g = 1; $g <= 8; $g++) {
            $datos['g_' . $g] = $mapa[$g] ?? 0;
        }

        DB::table('generational_bonuses')->updateOrInsert(['rank_bonus_id' => $rangoId], $datos);
    }
}
