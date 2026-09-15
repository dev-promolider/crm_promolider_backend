<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\RankBonus;
use App\Services\MLM\MembershipRules;
use App\Services\MLM\PlanSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * CompensationPlanController
 * Permite al administrador leer y editar toda la configuración del Plan de Compensación
 * directamente desde la base de datos, sin necesidad de tocar código.
 *
 * Tabla compartida: account_type, rank_bonus y generational_bonuses las sigue leyendo
 * el monolito (promolider.info), que usa la misma base. Por eso aquí nunca se renombra
 * ni se borra una columna, y lo que el monolito lee se mantiene sincronizado.
 */
class CompensationPlanController extends Controller
{
    public function __construct(private PlanSettings $settings)
    {
    }

    // ==========================================
    // MEMBRESÍAS (account_type)
    // ==========================================

    /**
     * Lista todas las membresías con su categoría, su OPC, sus PV y cuánta gente la tiene.
     * Endpoint: GET /admin/compensation/memberships
     */
    public function getMemberships()
    {
        $opc = DB::table('product')
            ->where('name', 'opc')
            ->whereNotNull('account_type_id')
            ->get(['account_type_id', 'price', 'points', 'status'])
            ->keyBy('account_type_id');

        $puntos = DB::table('account_type_points_money')->pluck('points', 'account_type_id');

        $usuarios = DB::table('users')
            ->select('id_account_type', DB::raw('COUNT(*) as total'))
            ->groupBy('id_account_type')
            ->pluck('total', 'id_account_type');

        $categorias = DB::table('membership_categories')->pluck('name', 'id');

        $memberships = AccountType::orderBy('sort_order')->orderBy('price')->get()
            ->map(function ($m) use ($opc, $puntos, $usuarios, $categorias) {
                return $this->formatearMembresia($m, $opc->get($m->id), $puntos->get($m->id), (int) ($usuarios->get($m->id) ?? 0), $categorias);
            });

        return response()->json([
            'data'       => $memberships,
            'categories' => $this->listaCategorias(),
        ]);
    }

    /**
     * Crea una membresía nueva, con su OPC y sus PV.
     * Endpoint: POST /admin/compensation/memberships
     */
    public function storeMembership(Request $request)
    {
        $validator = Validator::make($request->all(), $this->reglasMembresia(null));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($error = $this->comprobarCategoria($request->input('category_id'), null)) {
            return response()->json(['errors' => ['category_id' => [$error]]], 422);
        }

        $membresia = DB::transaction(function () use ($request) {
            $membresia = AccountType::create(array_merge($this->camposMembresia($request), [
                'account'                     => $request->input('account'),
                'price'                       => $request->input('price', 0),
                'iva'                         => $request->input('iva', $this->settings->iva()),
                'fast_cash_bonus'             => $request->input('fast_cash_bonus', 0),
                'pay_in_binary'               => $request->input('pay_in_binary', 0),
                'productor_bonus'             => $request->input('productor_bonus', 0),
                'course_selling_bonus'        => $request->input('course_selling_bonus', 0),
                'disc_purchases_course'       => $request->input('disc_purchases_course', 0),
                'disc_purchases_certificates' => $request->input('disc_purchases_certificates', 0),
                'enrollment_duration'         => $request->input('enrollment_duration', 12),
                'comission'                   => 0,
                'status'                      => (string) $request->input('status', '1'),
                'sort_order'                  => $request->input('sort_order', (int) AccountType::max('sort_order') + 10),
            ]));

            $this->sincronizarOpc($membresia, $request);
            $this->sincronizarPuntos($membresia, $request);

            return $membresia;
        });

        MembershipRules::olvidar();

        return response()->json([
            'message' => 'Membresía creada.',
            'data'    => $membresia->fresh(),
        ], 201);
    }

    /**
     * Actualiza una membresía, incluidos su OPC y sus PV.
     * Endpoint: PUT /admin/compensation/memberships/{id}
     */
    public function updateMembership(Request $request, $id)
    {
        $membresia = AccountType::findOrFail($id);

        $validator = Validator::make($request->all(), $this->reglasMembresia((int) $id));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // El pre-registro es la única membresía fija del sistema: se llama así y se queda
        // en su categoría. Todo lo demás, precio incluido, se puede cambiar.
        if ($membresia->system_key === 'preregistro') {
            if ($request->filled('account') && $request->input('account') !== $membresia->account) {
                return response()->json(['errors' => ['account' => ['El pre-registro no se puede renombrar.']]], 422);
            }
            if ($request->has('category_id') && (int) $request->input('category_id') !== (int) $membresia->category_id) {
                return response()->json(['errors' => ['category_id' => ['El pre-registro no se puede cambiar de categoría.']]], 422);
            }
        } elseif ($request->has('category_id') && ($error = $this->comprobarCategoria($request->input('category_id'), (int) $id))) {
            return response()->json(['errors' => ['category_id' => [$error]]], 422);
        }

        DB::transaction(function () use ($membresia, $request) {
            $datos = array_merge(
                $request->only([
                    'account', 'price', 'iva', 'fast_cash_bonus', 'pay_in_binary',
                    'productor_bonus', 'course_selling_bonus', 'disc_purchases_course',
                    'disc_purchases_certificates', 'enrollment_duration', 'sort_order',
                ]),
                $this->camposMembresia($request)
            );

            if ($request->has('status')) {
                $datos['status'] = $request->boolean('status') ? '1' : '0';
            }

            $membresia->update($datos);
            $membresia->refresh();

            $this->sincronizarOpc($membresia, $request);
            $this->sincronizarPuntos($membresia, $request);
        });

        MembershipRules::olvidar();

        return response()->json([
            'message' => 'Membresía actualizada.',
            'data'    => $membresia->fresh(),
        ]);
    }

    /**
     * Retira una membresía.
     * Endpoint: DELETE /admin/compensation/memberships/{id}
     *
     * Si alguien la tiene contratada no se borra: se retira (deja de ofrecerse y queda
     * inactiva) y quienes ya la tienen la conservan. Solo se borra de verdad si nadie
     * la ha usado nunca.
     */
    public function destroyMembership($id)
    {
        $membresia = AccountType::findOrFail($id);

        if ($membresia->system_key) {
            return response()->json(['message' => 'Esta membresía es del sistema y no se puede retirar.'], 422);
        }

        $enUso = DB::table('users')->where('id_account_type', $membresia->id)->exists();

        if ($enUso) {
            $membresia->update(['status' => '0', 'is_visible' => false]);
            MembershipRules::olvidar();

            return response()->json([
                'message' => 'Hay usuarios con esta membresía, así que se ha retirado en lugar de borrarla: deja de ofrecerse y quienes la tienen la conservan.',
                'data'    => $membresia->fresh(),
            ]);
        }

        DB::transaction(function () use ($membresia) {
            DB::table('product')->where('name', 'opc')->where('account_type_id', $membresia->id)->delete();
            DB::table('account_type_points_money')->where('account_type_id', $membresia->id)->delete();
            $membresia->delete();
        });

        MembershipRules::olvidar();

        return response()->json(['message' => 'Membresía eliminada.']);
    }

    // ==========================================
    // CATEGORÍAS DE MEMBRESÍA
    // ==========================================

    /**
     * Endpoint: GET /admin/compensation/membership-categories
     */
    public function getMembershipCategories()
    {
        return response()->json(['data' => $this->listaCategorias()]);
    }

    /**
     * Endpoint: POST /admin/compensation/membership-categories
     */
    public function storeMembershipCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug = Str::slug($request->input('name'));
        $base = $slug ?: 'categoria';
        $n = 2;
        while (DB::table('membership_categories')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        $id = DB::table('membership_categories')->insertGetId([
            'name'        => $request->input('name'),
            'slug'        => $slug,
            'description' => $request->input('description'),
            'sort_order'  => $request->input('sort_order', (int) DB::table('membership_categories')->max('sort_order') + 10),
            'status'      => $request->has('status') ? $request->boolean('status') : true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Categoría creada.',
            'data'    => DB::table('membership_categories')->find($id),
        ], 201);
    }

    /**
     * Endpoint: PUT /admin/compensation/membership-categories/{id}
     */
    public function updateMembershipCategory(Request $request, $id)
    {
        $categoria = DB::table('membership_categories')->find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($categoria->slug === 'preregistro' && $request->has('status') && !$request->boolean('status')) {
            return response()->json(['message' => 'La categoría de pre-registro no se puede desactivar.'], 422);
        }

        $datos = $request->only(['name', 'description', 'sort_order']);
        if ($request->has('status')) {
            $datos['status'] = $request->boolean('status');
        }
        $datos['updated_at'] = now();

        DB::table('membership_categories')->where('id', $id)->update($datos);

        return response()->json([
            'message' => 'Categoría actualizada.',
            'data'    => DB::table('membership_categories')->find($id),
        ]);
    }

    /**
     * Endpoint: DELETE /admin/compensation/membership-categories/{id}
     */
    public function destroyMembershipCategory($id)
    {
        $categoria = DB::table('membership_categories')->find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        if ($categoria->slug === 'preregistro') {
            return response()->json(['message' => 'La categoría de pre-registro no se puede borrar.'], 422);
        }

        if (AccountType::where('category_id', $id)->exists()) {
            return response()->json(['message' => 'Hay membresías en esta categoría. Muévelas a otra antes de borrarla.'], 422);
        }

        DB::table('membership_categories')->where('id', $id)->delete();

        return response()->json(['message' => 'Categoría eliminada.']);
    }

    // ==========================================
    // RANGOS (rank_bonus)
    // ==========================================

    /**
     * Lista todos los rangos con sus requisitos, sus porcentajes generacionales y si
     * están en uso.
     * Endpoint: GET /compensation/ranks
     */
    public function getRanks()
    {
        $ranks = RankBonus::orderBy('sort_order')->orderBy('id')->get([
            'id', 'name', 'sort_order', 'vol_min', 'active_direct', 'pack_max',
            'max_pay', 'monthly_bonus', 'monthly_bonus_months', 'monthly_bonus_frequency',
            'extra_bonus', 'limit_generation', 'icon', 'status'
        ]);

        $porcentajes = $this->porcentajesPorRango();

        $conHistorial = DB::table('rank_binary')->distinct()->pluck('rank_id')
            ->merge(DB::table('binary_cut_histories')->distinct()->pluck('rank_id'))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->all();

        $ranks->transform(function ($rank) use ($porcentajes, $conHistorial) {
            $rank->percentages = (object) ($porcentajes[(int) $rank->id] ?? []);
            $rank->en_uso = in_array((int) $rank->id, $conHistorial, true);
            $rank->status = (bool) $rank->status;

            return $rank;
        });

        return response()->json([
            'data'            => $ranks,
            'max_generations' => $this->settings->generacionesMaximas(),
        ]);
    }

    /**
     * Actualiza los parámetros de un rango, incluido el nombre y, si vienen, sus
     * porcentajes generacionales.
     * Endpoint: PUT /admin/compensation/ranks/{id}
     */
    public function updateRank(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->reglasRango((int) $id, false));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rank = RankBonus::findOrFail($id);

        DB::transaction(function () use ($rank, $request) {
            $datos = $request->only([
                'name', 'sort_order', 'vol_min', 'active_direct', 'pack_max', 'max_pay',
                'monthly_bonus', 'monthly_bonus_months', 'monthly_bonus_frequency',
                'limit_generation', 'icon',
            ]);

            if ($request->has('status')) {
                $datos['status'] = $request->boolean('status');
            }

            $rank->update($datos);
            $rank->refresh();

            if ($request->has('percentages')) {
                $this->guardarPorcentajes($rank, (array) $request->input('percentages'));
            } elseif ($request->filled('name')) {
                $this->sincronizarGeneracionalLegacy($rank, $this->porcentajesPorRango()[(int) $rank->id] ?? []);
            }
        });

        return response()->json([
            'message' => 'Rango actualizado.',
            'data'    => $rank->fresh(),
        ]);
    }

    /**
     * Crea un rango nuevo con sus porcentajes generacionales.
     * Endpoint: POST /admin/compensation/ranks
     */
    public function storeRank(Request $request)
    {
        $validator = Validator::make($request->all(), $this->reglasRango(null, true));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rank = DB::transaction(function () use ($request) {
            $rank = RankBonus::create([
                'name'                    => $request->input('name'),
                'sort_order'              => $request->input('sort_order', (int) RankBonus::max('sort_order') + 10),
                'vol_min'                 => $request->input('vol_min'),
                'active_direct'           => $request->input('active_direct'),
                'pack_max'                => $request->input('pack_max', 0),
                'max_pay'                 => $request->input('max_pay'),
                'monthly_bonus'           => $request->input('monthly_bonus', 0),
                'monthly_bonus_months'    => $request->input('monthly_bonus_months', 3),
                'monthly_bonus_frequency' => $request->input('monthly_bonus_frequency', 'monthly'),
                'extra_bonus'             => 0,
                'limit_generation'        => $request->input('limit_generation', 0),
                'icon'                    => $request->input('icon', ''),
                'status'                  => 1,
            ]);

            $this->guardarPorcentajes($rank, (array) $request->input('percentages', []));

            return $rank;
        });

        return response()->json([
            'message' => 'Rango creado.',
            'data'    => $rank->fresh(),
        ], 201);
    }

    /**
     * Retira un rango.
     * Endpoint: DELETE /admin/compensation/ranks/{id}
     *
     * Si el rango se ha llegado a asignar en algún corte no se borra: rank_binary y
     * binary_cut_histories lo referencian con clave foránea. Se desactiva, que a efectos
     * del corte es lo mismo —deja de asignarse— sin perder el historial.
     */
    public function destroyRank($id)
    {
        $rank = RankBonus::findOrFail($id);

        $enUso = DB::table('rank_binary')->where('rank_id', $rank->id)->exists()
            || DB::table('binary_cut_histories')->where('rank_id', $rank->id)->exists();

        if ($enUso) {
            $rank->update(['status' => 0]);

            return response()->json([
                'message' => 'El rango ya se había asignado en algún corte, así que se ha desactivado en lugar de borrarlo: deja de asignarse y el historial se sigue entendiendo.',
                'data'    => $rank->fresh(),
            ]);
        }

        DB::transaction(function () use ($rank) {
            DB::table('rank_generation_percentages')->where('rank_bonus_id', $rank->id)->delete();
            DB::table('generational_bonuses')->where('rank_bonus_id', $rank->id)->delete();
            $rank->delete();
        });

        return response()->json(['message' => 'Rango eliminado.']);
    }

    /**
     * Reordena los rangos de una vez.
     * Endpoint: PUT /admin/compensation/ranks-order
     */
    public function reorderRanks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orden'   => 'required|array|min:1',
            'orden.*' => 'required|integer|exists:rank_bonus,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request) {
            $posicion = 10;

            foreach ($request->input('orden') as $id) {
                RankBonus::where('id', $id)->update(['sort_order' => $posicion]);
                $posicion += 10;
            }
        });

        return response()->json([
            'message' => 'Orden de los rangos actualizado.',
            'data'    => RankBonus::orderBy('sort_order')->get(['id', 'name', 'sort_order']),
        ]);
    }

    /**
     * Sube o cambia la insignia de un rango.
     * Endpoint: POST /admin/compensation/ranks/{id}/icon
     *
     * Es la imagen que ve el afiliado arriba en el panel cuando alcanza el rango. En el
     * monolito se gestionaba desde «Rango Bonos»; en el CRM nuevo no había forma.
     */
    public function uploadRankIcon(Request $request, $id)
    {
        $rank = RankBonus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'icon' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $archivo = $request->file('icon');
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'png');
        $ruta = 'images/ranks/rank-' . $rank->id . '-' . time() . '.' . $extension;

        try {
            Storage::disk('s3')->put($ruta, file_get_contents($archivo->getRealPath()), ['visibility' => 'public']);
        } catch (\Throwable $e) {
            Log::error('[RANGOS] No se pudo subir la insignia', ['rango' => $rank->id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'No se pudo subir la insignia: ' . $e->getMessage()], 500);
        }

        $rank->update(['icon' => $ruta]);

        return response()->json([
            'message' => 'Insignia actualizada.',
            'data'    => ['icon' => $ruta],
        ]);
    }

    // ==========================================
    // BONO GENERACIONAL
    // ==========================================

    /**
     * Porcentajes generacionales de todos los rangos activos.
     * Endpoint: GET /compensation/generational-bonuses
     *
     * Devuelve los porcentajes como mapa generación => porcentaje, sin tope de columnas.
     * Se mantienen g_1..g_8 para quien todavía lea el formato antiguo.
     */
    public function getGenerationalBonuses()
    {
        $porcentajes = $this->porcentajesPorRango();

        $filas = RankBonus::where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'limit_generation'])
            ->map(function ($rank) use ($porcentajes) {
                $mapa = $porcentajes[(int) $rank->id] ?? [];

                $fila = [
                    'id'               => (int) $rank->id,
                    'rank_bonus_id'    => (int) $rank->id,
                    'rank_name'        => $rank->name,
                    'limit_generation' => (int) $rank->limit_generation,
                    'percentages'      => (object) $mapa,
                ];

                for ($g = 1; $g <= 8; $g++) {
                    $fila['g_' . $g] = $mapa[$g] ?? 0;
                }

                return $fila;
            });

        return response()->json([
            'data'            => $filas,
            'max_generations' => $this->settings->generacionesMaximas(),
        ]);
    }

    /**
     * Guarda los porcentajes generacionales de un rango.
     * Endpoint: PUT /admin/compensation/ranks/{id}/generations
     */
    public function updateRankGenerations(Request $request, $id)
    {
        $rank = RankBonus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'percentages'   => 'present|array',
            'percentages.*' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $maximo = $this->settings->generacionesMaximas();

        foreach (array_keys((array) $request->input('percentages')) as $generacion) {
            if (!ctype_digit((string) $generacion) || (int) $generacion < 1 || (int) $generacion > $maximo) {
                return response()->json([
                    'errors' => ['percentages' => ["La generación {$generacion} está fuera del plan (1 a {$maximo})."]],
                ], 422);
            }
        }

        DB::transaction(function () use ($rank, $request) {
            $this->guardarPorcentajes($rank, (array) $request->input('percentages'));
        });

        return response()->json([
            'message' => 'Porcentajes generacionales guardados.',
            'data'    => (object) ($this->porcentajesPorRango()[(int) $rank->id] ?? []),
        ]);
    }

    // ==========================================
    // AJUSTES DEL PLAN
    // ==========================================

    /**
     * Endpoint: GET /admin/compensation/settings
     */
    public function getPlanSettings()
    {
        return response()->json(['data' => $this->settings->todos()]);
    }

    /**
     * Ajustes del plan que no son del calendario del corte.
     * Endpoint: PUT /admin/compensation/settings
     */
    public function updatePlanSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            PlanSettings::GENERACIONES_MAXIMAS          => 'sometimes|integer|min:1|max:30',
            PlanSettings::GENERACIONAL_NIVEL_ALTO_DESDE => 'sometimes|integer|min:0|max:30',
            PlanSettings::RANGO_ALCANCE_DIRECTOS        => 'sometimes|in:directos,red',
            PlanSettings::RANGO_ALCANCE_NIVEL_ALTO      => 'sometimes|in:directos,red',
            PlanSettings::PV_POR_USD_CURSO              => 'sometimes|numeric|min:0|max:100',
            PlanSettings::IVA                           => 'sometimes|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $claves = [
            PlanSettings::GENERACIONES_MAXIMAS,
            PlanSettings::GENERACIONAL_NIVEL_ALTO_DESDE,
            PlanSettings::RANGO_ALCANCE_DIRECTOS,
            PlanSettings::RANGO_ALCANCE_NIVEL_ALTO,
            PlanSettings::PV_POR_USD_CURSO,
            PlanSettings::IVA,
        ];

        // Reducir el número de generaciones por debajo de lo que ya desbloquea algún
        // rango dejaría porcentajes guardados que no se ven ni se pagan.
        if ($request->has(PlanSettings::GENERACIONES_MAXIMAS)) {
            $nuevo = (int) $request->input(PlanSettings::GENERACIONES_MAXIMAS);
            $usado = (int) RankBonus::max('limit_generation');

            if ($nuevo < $usado) {
                return response()->json([
                    'errors' => [PlanSettings::GENERACIONES_MAXIMAS => ["Hay rangos que desbloquean {$usado} generaciones. Bájalos antes de reducir el plan a {$nuevo}."]],
                ], 422);
            }
        }

        foreach ($request->only($claves) as $clave => $valor) {
            $this->settings->set($clave, (string) $valor);
        }

        return response()->json([
            'message' => 'Ajustes del plan guardados.',
            'data'    => $this->settings->todos(),
        ]);
    }

    // ==========================================
    // ENDPOINT PÚBLICO: Plan de Membresías
    // ==========================================

    /**
     * Las membresías que se ofrecen al registrarse.
     * Sin autenticación requerida.
     * Endpoint: GET /public/membership-plans
     *
     * Antes filtraba por nombre (School, Academy, University, Socio Fundador), así que
     * una membresía creada o renombrada desde el panel no aparecía nunca. Ahora sale lo
     * que el administrador marca como visible.
     */
    public function publicMembershipPlans()
    {
        $opc = DB::table('product')->where('name', 'opc')->where('status', '1')->get()->keyBy('account_type_id');
        $puntos = DB::table('account_type_points_money')->pluck('points', 'account_type_id');
        $categorias = DB::table('membership_categories')->pluck('name', 'id');

        $memberships = AccountType::where('status', '1')
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get()
            ->map(function ($m) use ($opc, $puntos, $categorias) {
                $producto = $m->requires_opc ? $opc->get($m->id) : null;

                return [
                    'id'                   => $m->id,
                    'account'              => $m->account,
                    'description'          => $m->description,
                    'category'             => $categorias->get($m->category_id),
                    'price'                => (float) $m->price,
                    'iva'                  => (float) $m->iva,
                    'fast_cash_bonus'      => (float) $m->fast_cash_bonus,
                    'pay_in_binary'        => (float) $m->pay_in_binary,
                    'productor_bonus'      => (float) $m->productor_bonus,
                    'course_selling_bonus' => (float) $m->course_selling_bonus,
                    'enrollment_duration'  => (int) $m->enrollment_duration,
                    'is_permanent'         => (bool) $m->is_permanent,
                    'requires_opc'         => (bool) $m->requires_opc,
                    'opc_price'            => $producto ? (float) $producto->price : null,
                    'opc_points'           => $producto ? (int) $producto->points : null,
                    'affiliation_points'   => (float) ($puntos->get($m->id) ?? 0),
                    'highlight_label'      => $m->highlight_label,
                ];
            });

        return response()->json(['data' => $memberships]);
    }

    // ==========================================
    // Auxiliares
    // ==========================================

    private function formatearMembresia(AccountType $m, $producto, $puntos, int $usuarios, $categorias): array
    {
        return [
            'id'                          => (int) $m->id,
            'account'                     => $m->account,
            'category_id'                 => $m->category_id ? (int) $m->category_id : null,
            'category'                    => $m->category_id ? $categorias->get($m->category_id) : null,
            'system_key'                  => $m->system_key,
            'description'                 => $m->description,
            'sort_order'                  => (int) $m->sort_order,
            'price'                       => (float) $m->price,
            'iva'                         => (float) $m->iva,
            'fast_cash_bonus'             => (float) $m->fast_cash_bonus,
            'pay_in_binary'               => (float) $m->pay_in_binary,
            'productor_bonus'             => (float) $m->productor_bonus,
            'course_selling_bonus'        => (float) $m->course_selling_bonus,
            'disc_purchases_course'       => (float) $m->disc_purchases_course,
            'disc_purchases_certificates' => (float) $m->disc_purchases_certificates,
            'enrollment_duration'         => (int) $m->enrollment_duration,
            'status'                      => (string) $m->status === '1',
            'is_visible'                  => (bool) $m->is_visible,
            'is_permanent'                => (bool) $m->is_permanent,
            'requires_opc'                => (bool) $m->requires_opc,
            'feeds_network'               => (bool) $m->feeds_network,
            'counts_as_top_tier'          => (bool) $m->counts_as_top_tier,
            'max_members'                 => $m->max_members !== null ? (int) $m->max_members : null,
            'highlight_label'             => $m->highlight_label,
            'highlight_color'             => $m->highlight_color,
            'opc_price'                   => $producto && (string) $producto->status === '1' ? (float) $producto->price : null,
            'opc_points'                  => $producto && (string) $producto->status === '1' ? (int) $producto->points : null,
            'affiliation_points'          => (float) ($puntos ?? 0),
            'users_count'                 => $usuarios,
            // Aviso para el panel: exige OPC pero no hay con qué pagarlo.
            'opc_sin_precio'              => (bool) $m->requires_opc && (float) $m->price > 0
                                             && !($producto && (string) $producto->status === '1'),
        ];
    }

    private function listaCategorias(): array
    {
        $cuantas = AccountType::select('category_id', DB::raw('COUNT(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return DB::table('membership_categories')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($c) use ($cuantas) {
                $c->status = (bool) $c->status;
                $c->memberships_count = (int) ($cuantas->get($c->id) ?? 0);
                return $c;
            })
            ->all();
    }

    private function reglasMembresia(?int $id): array
    {
        $requerido = $id === null ? 'required' : 'sometimes';

        return [
            'account'                     => $requerido . '|string|max:255|unique:account_type,account' . ($id ? ',' . $id : ''),
            'category_id'                 => 'nullable|integer|exists:membership_categories,id',
            'description'                 => 'nullable|string|max:2000',
            'sort_order'                  => 'nullable|integer|min:0',
            'price'                       => 'nullable|numeric|min:0',
            'iva'                         => 'nullable|numeric|min:0|max:100',
            'fast_cash_bonus'             => 'nullable|numeric|min:0|max:100',
            'pay_in_binary'               => 'nullable|numeric|min:0|max:100',
            'productor_bonus'             => 'nullable|numeric|min:0|max:100',
            'course_selling_bonus'        => 'nullable|numeric|min:0|max:100',
            'disc_purchases_course'       => 'nullable|numeric|min:0|max:100',
            'disc_purchases_certificates' => 'nullable|numeric|min:0|max:100',
            'enrollment_duration'         => 'nullable|integer|min:1|max:600',
            'status'                      => 'nullable|boolean',
            'is_visible'                  => 'nullable|boolean',
            'is_permanent'                => 'nullable|boolean',
            'requires_opc'                => 'nullable|boolean',
            'feeds_network'               => 'nullable|boolean',
            'counts_as_top_tier'          => 'nullable|boolean',
            'max_members'                 => 'nullable|integer|min:1',
            'highlight_label'             => 'nullable|string|max:60',
            'highlight_color'             => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'opc_price'                   => 'nullable|numeric|min:0',
            'opc_points'                  => 'nullable|integer|min:0',
            'affiliation_points'          => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Columnas nuevas de la membresía que llegan como casillas o textos opcionales.
     */
    private function camposMembresia(Request $request): array
    {
        $datos = [];

        foreach (['is_visible', 'is_permanent', 'requires_opc', 'feeds_network', 'counts_as_top_tier'] as $casilla) {
            if ($request->has($casilla)) {
                $datos[$casilla] = $request->boolean($casilla);
            }
        }

        foreach (['category_id', 'description', 'max_members', 'highlight_label', 'highlight_color'] as $campo) {
            if ($request->has($campo)) {
                $valor = $request->input($campo);
                $datos[$campo] = $valor === '' ? null : $valor;
            }
        }

        return $datos;
    }

    /**
     * Solo puede haber una membresía en la categoría de pre-registro.
     */
    private function comprobarCategoria($categoryId, ?int $membresiaId): ?string
    {
        if (!$categoryId) {
            return null;
        }

        $categoria = DB::table('membership_categories')->find($categoryId);

        if (!$categoria || $categoria->slug !== 'preregistro') {
            return null;
        }

        $otra = AccountType::where('category_id', $categoria->id)
            ->when($membresiaId, fn ($q) => $q->where('id', '!=', $membresiaId))
            ->exists();

        return $otra ? 'Solo puede haber una membresía de pre-registro.' : null;
    }

    /**
     * El OPC vive dentro de la membresía: si la membresía lo lleva se guarda su precio y
     * sus PV; si no, el producto se desactiva. Se sigue guardando en la tabla product
     * porque es donde lo leen los cobros de OPC (tarjeta y billetera) y el monolito.
     */
    private function sincronizarOpc(AccountType $membresia, Request $request): void
    {
        if (!$request->hasAny(['requires_opc', 'opc_price', 'opc_points'])) {
            return;
        }

        $producto = DB::table('product')
            ->where('name', 'opc')
            ->where('account_type_id', $membresia->id)
            ->first();

        if (!$membresia->requires_opc) {
            if ($producto) {
                DB::table('product')->where('id', $producto->id)->update(['status' => '0', 'updated_at' => now()]);
            }
            return;
        }

        $precio = $request->input('opc_price', $producto->price ?? null);
        $puntos = $request->input('opc_points', $producto->points ?? 0);

        if ($precio === null || $precio === '') {
            return;
        }

        if ($producto) {
            DB::table('product')->where('id', $producto->id)->update([
                'price'      => $precio,
                'points'     => (int) $puntos,
                'status'     => '1',
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('product')->insert([
            'account_type_id'  => $membresia->id,
            'name'             => 'opc',
            'descripcion'      => Str::limit('OPC ' . $membresia->account, 100, ''),
            'price'            => $precio,
            'promotion_prince' => 0,
            'commission'       => 0,
            'status'           => '1',
            'points'           => (int) $puntos,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /**
     * PV que genera la afiliación con esta membresía.
     */
    private function sincronizarPuntos(AccountType $membresia, Request $request): void
    {
        if (!$request->has('affiliation_points')) {
            return;
        }

        $puntos = (float) $request->input('affiliation_points', 0);
        $fila = DB::table('account_type_points_money')->where('account_type_id', $membresia->id)->first();

        if ($fila) {
            DB::table('account_type_points_money')->where('id', $fila->id)->update([
                'points'     => $puntos,
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('account_type_points_money')->insert([
            'account_type_id' => $membresia->id,
            'points'          => $puntos,
            'money'           => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function reglasRango(?int $id, bool $alta): array
    {
        $maximo = $this->settings->generacionesMaximas();
        $requerido = $alta ? 'required' : 'nullable';

        return [
            'name'                    => ($alta ? 'required' : 'sometimes') . '|string|max:255|unique:rank_bonus,name' . ($id ? ',' . $id : ''),
            'sort_order'              => 'nullable|integer|min:0',
            'vol_min'                 => $requerido . '|numeric|min:0',
            'active_direct'           => $requerido . '|integer|min:0',
            'pack_max'                => 'nullable|integer|min:0',
            'max_pay'                 => $requerido . '|numeric|min:0',
            'monthly_bonus'           => 'nullable|numeric|min:0',
            'monthly_bonus_months'    => 'nullable|integer|min:1|max:36',
            'monthly_bonus_frequency' => 'nullable|in:monthly,quarterly',
            'limit_generation'        => 'nullable|integer|min:0|max:' . $maximo,
            'icon'                    => 'nullable|string|max:255',
            'status'                  => 'nullable|boolean',
            'percentages'             => 'sometimes|array',
            'percentages.*'           => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * @return array<int, array<int, float>>
     */
    private function porcentajesPorRango(): array
    {
        $mapa = [];

        foreach (DB::table('rank_generation_percentages')->orderBy('generation')->get() as $fila) {
            $mapa[(int) $fila->rank_bonus_id][(int) $fila->generation] = (float) $fila->percentage;
        }

        return $mapa;
    }

    /**
     * Sustituye los porcentajes de un rango. Las generaciones en cero no se guardan.
     */
    private function guardarPorcentajes(RankBonus $rank, array $porcentajes): void
    {
        $maximo = $this->settings->generacionesMaximas();
        $limpios = [];

        foreach ($porcentajes as $generacion => $valor) {
            $g = (int) $generacion;
            $v = (float) $valor;

            if ($g >= 1 && $g <= $maximo && $v > 0) {
                $limpios[$g] = $v;
            }
        }

        DB::table('rank_generation_percentages')->where('rank_bonus_id', $rank->id)->delete();

        foreach ($limpios as $g => $v) {
            DB::table('rank_generation_percentages')->insert([
                'rank_bonus_id' => $rank->id,
                'generation'    => $g,
                'percentage'    => $v,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->sincronizarGeneracionalLegacy($rank, $limpios);
    }

    /**
     * El monolito sigue leyendo generational_bonuses con sus ocho columnas. Se mantiene
     * al día hasta la octava generación; lo que pase de ahí solo existe en la tabla nueva.
     */
    private function sincronizarGeneracionalLegacy(RankBonus $rank, array $porcentajes): void
    {
        $datos = ['range_name' => $rank->name, 'updated_at' => now()];

        for ($g = 1; $g <= 8; $g++) {
            $datos['g_' . $g] = $porcentajes[$g] ?? 0;
        }

        $existe = DB::table('generational_bonuses')->where('rank_bonus_id', $rank->id)->exists();

        if ($existe) {
            DB::table('generational_bonuses')->where('rank_bonus_id', $rank->id)->update($datos);
            return;
        }

        DB::table('generational_bonuses')->insert($datos + [
            'rank_bonus_id' => $rank->id,
            'created_at'    => now(),
        ]);
    }
}
