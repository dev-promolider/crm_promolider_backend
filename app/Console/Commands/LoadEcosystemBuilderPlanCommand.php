<?php

namespace App\Console\Commands;

use App\Models\AccountType;
use App\Models\RankBonus;
use App\Services\MLM\MembershipRules;
use App\Services\MLM\PlanSettings;
use App\Services\MLM\PlanVersionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga en la configuración el plan nuevo, «Ecosystem Builder System™».
 *
 * No se ejecuta solo ni en ninguna migración: cargar el plan cambia lo que el sistema
 * paga y hay preguntas de negocio sin responder. Sin --aplicar solo enseña lo que
 * cambiaría. Con --aplicar guarda antes una versión del plan, así que se puede
 * deshacer desde el panel.
 *
 * Supuestos, a confirmar con el equipo antes de aplicarlo:
 *   - Los rangos existentes se renombran por su orden: el segundo pasa a Builder, el
 *     tercero a Elite Builder y así hasta Crown Legacy. El primero (Aprendiz) se deja
 *     como rango de partida. Founder Legend se crea desactivado: el plan lo reserva y
 *     no tiene requisitos numéricos.
 *   - Las membresías del plan se crean nuevas y ocultas. Nadie cambia de membresía:
 *     pasar a los usuarios actuales es otra decisión.
 *   - Precios tal cual los da el plan y con IGV 0, porque sus ejemplos calculan los
 *     porcentajes sobre el precio completo ($387.50 = 25% de $1,550).
 *   - START y FOUNDERS LEGACY no llevan OPC; FOUNDERS LEGACY es de pago único, con 100
 *     plazas y distintivo propio.
 */
class LoadEcosystemBuilderPlanCommand extends Command
{
    protected $signature = 'plan:ecosystem-builder
                            {--aplicar : Escribir los cambios (antes se guarda una versión del plan)}
                            {--force : No pedir confirmación al aplicar}';

    protected $description = 'Carga los rangos, porcentajes y membresías del plan Ecosystem Builder (sin --aplicar solo muestra los cambios)';

    private const GENERACIONES = [1 => 5, 2 => 5, 3 => 5, 4 => 3, 5 => 2, 6 => 1, 7 => 1, 8 => 1];

    private const RANGOS = [
        ['name' => 'Builder',             'vol_min' => 70,      'active_direct' => 2,  'pack_max' => 0,    'max_pay' => 500,    'monthly_bonus' => 0,     'freq' => 'monthly',   'gen' => 1],
        ['name' => 'Elite Builder',       'vol_min' => 840,     'active_direct' => 2,  'pack_max' => 1,    'max_pay' => 1000,   'monthly_bonus' => 0,     'freq' => 'monthly',   'gen' => 2],
        ['name' => 'Master Builder',      'vol_min' => 2800,    'active_direct' => 3,  'pack_max' => 2,    'max_pay' => 2000,   'monthly_bonus' => 0,     'freq' => 'monthly',   'gen' => 3],
        ['name' => 'Ecosystem Architect', 'vol_min' => 9800,    'active_direct' => 7,  'pack_max' => 5,    'max_pay' => 5000,   'monthly_bonus' => 0,     'freq' => 'monthly',   'gen' => 4],
        ['name' => 'Community Leader',    'vol_min' => 20650,   'active_direct' => 9,  'pack_max' => 12,   'max_pay' => 10000,  'monthly_bonus' => 0,     'freq' => 'monthly',   'gen' => 5],
        ['name' => 'Global Leader',       'vol_min' => 63000,   'active_direct' => 12, 'pack_max' => 30,   'max_pay' => 30000,  'monthly_bonus' => 2000,  'freq' => 'monthly',   'gen' => 6],
        ['name' => 'Visionary Leader',    'vol_min' => 126000,  'active_direct' => 15, 'pack_max' => 80,   'max_pay' => 50000,  'monthly_bonus' => 4000,  'freq' => 'monthly',   'gen' => 7],
        ['name' => 'Crown Leader',        'vol_min' => 259000,  'active_direct' => 18, 'pack_max' => 150,  'max_pay' => 75000,  'monthly_bonus' => 7000,  'freq' => 'monthly',   'gen' => 8],
        ['name' => 'Legacy Builder',      'vol_min' => 777000,  'active_direct' => 25, 'pack_max' => 420,  'max_pay' => 80000,  'monthly_bonus' => 10000, 'freq' => 'monthly',   'gen' => 8],
        ['name' => 'Global Legacy',       'vol_min' => 880000,  'active_direct' => 30, 'pack_max' => 980,  'max_pay' => 100000, 'monthly_bonus' => 15000, 'freq' => 'monthly',   'gen' => 8],
        ['name' => 'Crown Legacy',        'vol_min' => 1000000, 'active_direct' => 40, 'pack_max' => 1500, 'max_pay' => 200000, 'monthly_bonus' => 15000, 'freq' => 'quarterly', 'gen' => 8],
    ];

    private const MEMBRESIAS = [
        ['account' => 'START',           'price' => 50,   'pv' => 15,  'fast' => 10, 'disc' => 0,  'venta' => 0,  'binario' => 0,  'opc' => false, 'permanente' => false, 'alto' => false, 'plazas' => null, 'distintivo' => null,              'descripcion' => 'Founding Member. Reserva tu posición estratégica.'],
        ['account' => 'PRO',             'price' => 290,  'pv' => 85,  'fast' => 15, 'disc' => 15, 'venta' => 15, 'binario' => 15, 'opc' => true,  'permanente' => false, 'alto' => false, 'plazas' => null, 'distintivo' => null,              'descripcion' => 'Independent Ecosystem Builder. Activa tu ecosistema.'],
        ['account' => 'ELITE',           'price' => 890,  'pv' => 258, 'fast' => 20, 'disc' => 20, 'venta' => 20, 'binario' => 20, 'opc' => true,  'permanente' => false, 'alto' => false, 'plazas' => null, 'distintivo' => null,              'descripcion' => 'Escala tu crecimiento e impacto.'],
        ['account' => 'CROWN',           'price' => 1550, 'pv' => 450, 'fast' => 25, 'disc' => 25, 'venta' => 25, 'binario' => 25, 'opc' => true,  'permanente' => false, 'alto' => true,  'plazas' => null, 'distintivo' => null,              'descripcion' => 'Liderazgo y expansión global.'],
        ['account' => 'FOUNDERS LEGACY', 'price' => 2560, 'pv' => 743, 'fast' => 25, 'disc' => 25, 'venta' => 25, 'binario' => 25, 'opc' => false, 'permanente' => true,  'alto' => true,  'plazas' => 100,  'distintivo' => 'FOUNDERS LEGACY', 'descripcion' => 'Socio fundador visionario. Pago único, limitado a las primeras 100 posiciones.'],
    ];

    private const TIPOS_DE_BONO = [
        1 => 'Fast Start Bonus (efectivo rápido)',
        2 => 'Marketplace Profit Bonus (venta de cursos)',
        3 => 'Creator Royalty Bonus (propiedad intelectual)',
        4 => 'Ecosystem Builder Bonus (binario)',
        5 => 'Leadership Legacy Bonus (generacional)',
        7 => 'Bono de estabilidad de rango',
    ];

    /** @var array<int, string> */
    private array $cambios = [];

    /** @var array<int, callable> */
    private array $acciones = [];

    public function handle(PlanVersionService $versiones, PlanSettings $settings)
    {
        $this->planificarRangos();
        $this->planificarMembresias();
        $this->planificarAjustes($settings);

        $this->line('');
        $this->info('Plan Ecosystem Builder: ' . count($this->cambios) . ' cambios');
        foreach ($this->cambios as $cambio) {
            $this->line('  · ' . $cambio);
        }

        if (!$this->option('aplicar')) {
            $this->line('');
            $this->line('No se ha tocado nada. Para aplicarlo: php artisan plan:ecosystem-builder --aplicar');

            return 0;
        }

        if (!$this->option('force') && !$this->confirm('Esto cambia lo que el sistema paga. Se guardará antes una versión del plan. ¿Continuar?', false)) {
            $this->warn('Cancelado.');

            return 1;
        }

        $respaldo = $versiones->guardar('Antes de cargar el plan Ecosystem Builder', null, null, true);

        DB::transaction(function () {
            foreach ($this->acciones as $accion) {
                $accion();
            }
        });

        MembershipRules::olvidar();
        PlanSettings::olvidarCache();

        $this->info('Plan cargado. Si hay que deshacerlo, está guardada la versión #' . $respaldo . ' en el panel.');

        return 0;
    }

    private function planificarRangos(): void
    {
        $existentes = RankBonus::orderBy('sort_order')->orderBy('id')->get()->values();
        $base = $existentes->first();
        $resto = $existentes->slice(1)->values();

        foreach (self::RANGOS as $i => $plan) {
            $actual = $resto->get($i);

            if ($actual) {
                $this->cambios[] = "Rango «{$actual->name}» → «{$plan['name']}» ({$plan['vol_min']} PV, {$plan['active_direct']} directos, {$plan['pack_max']} de nivel alto, tope \${$plan['max_pay']})";
                $this->acciones[] = fn () => $this->escribirRango($actual->id, $plan);
            } else {
                $this->cambios[] = "Rango nuevo «{$plan['name']}»";
                $this->acciones[] = fn () => $this->escribirRango(null, $plan);
            }
        }

        foreach ($resto->slice(count(self::RANGOS)) as $sobrante) {
            if ($sobrante->name === 'Founder Legend') {
                continue;
            }
            $this->cambios[] = "Rango «{$sobrante->name}» se desactiva (el plan no lo tiene)";
            $this->acciones[] = fn () => RankBonus::where('id', $sobrante->id)->update(['status' => 0]);
        }

        if (!RankBonus::where('name', 'Founder Legend')->exists()) {
            $this->cambios[] = 'Rango nuevo «Founder Legend», desactivado: el plan lo reserva y no tiene requisitos numéricos';
            $this->acciones[] = function () {
                $id = $this->escribirRango(null, [
                    'name' => 'Founder Legend', 'vol_min' => 0, 'active_direct' => 0, 'pack_max' => 0,
                    'max_pay' => 200000, 'monthly_bonus' => 0, 'freq' => 'monthly', 'gen' => 8,
                ]);
                RankBonus::where('id', $id)->update(['status' => 0]);
            };
        }

        if ($base) {
            $this->cambios[] = "Rango de partida «{$base->name}» se mantiene (quien aún no llega a Builder)";
        }
    }

    private function escribirRango(?int $id, array $plan): int
    {
        $datos = [
            'name'                    => $plan['name'],
            'vol_min'                 => $plan['vol_min'],
            'active_direct'           => $plan['active_direct'],
            'pack_max'                => $plan['pack_max'],
            'max_pay'                 => $plan['max_pay'],
            'monthly_bonus'           => $plan['monthly_bonus'],
            'monthly_bonus_months'    => 3,
            'monthly_bonus_frequency' => $plan['freq'],
            'limit_generation'        => $plan['gen'],
            'status'                  => 1,
        ];

        if ($id) {
            RankBonus::where('id', $id)->update($datos);
        } else {
            $id = RankBonus::create($datos + [
                'sort_order'  => (int) RankBonus::max('sort_order') + 10,
                'extra_bonus' => 0,
                'icon'        => '',
            ])->id;
        }

        DB::table('rank_generation_percentages')->where('rank_bonus_id', $id)->delete();

        $legacy = ['range_name' => $plan['name'], 'updated_at' => now()];

        for ($g = 1; $g <= 8; $g++) {
            $valor = $g <= $plan['gen'] ? self::GENERACIONES[$g] : 0;
            $legacy['g_' . $g] = $valor;

            if ($valor > 0) {
                DB::table('rank_generation_percentages')->insert([
                    'rank_bonus_id' => $id,
                    'generation'    => $g,
                    'percentage'    => $valor,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        DB::table('generational_bonuses')->updateOrInsert(['rank_bonus_id' => $id], $legacy);

        return (int) $id;
    }

    private function planificarMembresias(): void
    {
        $constructor = DB::table('membership_categories')->where('slug', 'constructor')->value('id');

        foreach (self::MEMBRESIAS as $plan) {
            $existe = AccountType::whereRaw('LOWER(account) = ?', [mb_strtolower($plan['account'])])->exists();

            if ($existe) {
                $this->cambios[] = "Membresía «{$plan['account']}» ya existe: no se toca";
                continue;
            }

            $this->cambios[] = "Membresía nueva «{$plan['account']}» \${$plan['price']}, {$plan['pv']} PV, oculta hasta decidir"
                . ($plan['opc'] ? ', con OPC $60 / 15 PV' : ', sin OPC')
                . ($plan['permanente'] ? ', pago único' : '');

            $this->acciones[] = function () use ($plan, $constructor) {
                $membresia = AccountType::create([
                    'account'                     => $plan['account'],
                    'category_id'                 => $constructor,
                    'description'                 => $plan['descripcion'],
                    'sort_order'                  => (int) AccountType::max('sort_order') + 10,
                    'price'                       => $plan['price'],
                    'iva'                         => 0,
                    'fast_cash_bonus'             => $plan['fast'],
                    'disc_purchases_course'       => $plan['disc'],
                    'course_selling_bonus'        => $plan['venta'],
                    'pay_in_binary'               => $plan['binario'],
                    'productor_bonus'             => 30,
                    'disc_purchases_certificates' => 0,
                    'comission'                   => 0,
                    'enrollment_duration'         => 12,
                    'status'                      => '1',
                    'is_visible'                  => false,
                    'is_permanent'                => $plan['permanente'],
                    'requires_opc'                => $plan['opc'],
                    'feeds_network'               => true,
                    'counts_as_top_tier'          => $plan['alto'],
                    'max_members'                 => $plan['plazas'],
                    'highlight_label'             => $plan['distintivo'],
                    'highlight_color'             => $plan['distintivo'] ? '#d4a017' : null,
                ]);

                DB::table('account_type_points_money')->insert([
                    'account_type_id' => $membresia->id,
                    'points'          => $plan['pv'],
                    'money'           => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                if ($plan['opc']) {
                    DB::table('product')->insert([
                        'account_type_id'  => $membresia->id,
                        'name'             => 'opc',
                        'descripcion'      => 'OPC ' . $plan['account'],
                        'price'            => 60,
                        'promotion_prince' => 0,
                        'commission'       => 0,
                        'status'           => '1',
                        'points'           => 15,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            };
        }
    }

    private function planificarAjustes(PlanSettings $settings): void
    {
        if ($settings->generacionalNivelAltoDesde() !== 3) {
            $this->cambios[] = 'El bono de liderazgo exige nivel alto desde la 3.ª generación';
            $this->acciones[] = fn () => $settings->set(PlanSettings::GENERACIONAL_NIVEL_ALTO_DESDE, '3');
        }

        if ($settings->generacionesMaximas() < 8) {
            $this->cambios[] = 'El plan pasa a tener 8 generaciones';
            $this->acciones[] = fn () => $settings->set(PlanSettings::GENERACIONES_MAXIMAS, '8');
        }

        foreach (self::TIPOS_DE_BONO as $id => $nombre) {
            $actual = DB::table('bonus_type')->where('id', $id)->value('description');

            if ($actual !== null && $actual !== $nombre) {
                $this->cambios[] = "Tipo de bono {$id}: «{$actual}» → «{$nombre}»";
                $this->acciones[] = fn () => DB::table('bonus_type')->where('id', $id)->update(['description' => $nombre, 'updated_at' => now()]);
            }
        }
    }
}
