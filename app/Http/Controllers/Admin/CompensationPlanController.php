<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\Product;
use App\Models\RankBonus;
use App\Services\MLM\PlanVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * CompensationPlanController
 * Permite al administrador leer y editar toda la configuración del Plan de Compensación
 * directamente desde la base de datos, sin necesidad de tocar código.
 */
class CompensationPlanController extends Controller
{
    // ==========================================
    // MEMBRESÍAS (account_type)
    // ==========================================

    /**
     * Lista todas las membresías con sus precios, bonos y porcentajes.
     * Endpoint: GET /admin/compensation/memberships
     */
    public function getMemberships()
    {
        $memberships = AccountType::orderBy('price')->get([
            'id', 'account', 'price', 'iva',
            'fast_cash_bonus', 'pay_in_binary',
            'productor_bonus', 'course_selling_bonus',
            'disc_purchases_course', 'disc_purchases_certificates',
            'enrollment_duration', 'status'
        ]);

        return response()->json(['data' => $memberships]);
    }

    /**
     * Actualiza precio y porcentajes de una membresía.
     * Endpoint: PUT /admin/compensation/memberships/{id}
     */
    public function updateMembership(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price'                     => 'nullable|numeric|min:0',
            'fast_cash_bonus'           => 'nullable|numeric|min:0|max:100',
            'pay_in_binary'             => 'nullable|numeric|min:0|max:100',
            'productor_bonus'           => 'nullable|numeric|min:0|max:100',
            'course_selling_bonus'      => 'nullable|numeric|min:0|max:100',
            'disc_purchases_course'     => 'nullable|numeric|min:0|max:100',
            'disc_purchases_certificates' => 'nullable|numeric|min:0|max:100',
            'enrollment_duration'       => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $membership = AccountType::findOrFail($id);
        $membership->update($request->only([
            'price', 'fast_cash_bonus', 'pay_in_binary',
            'productor_bonus', 'course_selling_bonus',
            'disc_purchases_course', 'disc_purchases_certificates',
            'enrollment_duration'
        ]));

        return response()->json([
            'message' => 'Membresía actualizada exitosamente.',
            'data'    => $membership->fresh()
        ]);
    }

    // ==========================================
    // PRODUCTOS OPC (product)
    // ==========================================

    /**
     * Lista los precios y puntos del OPC por tipo de membresía.
     * Endpoint: GET /admin/compensation/opc-products
     */
    public function getOpcProducts()
    {
        $products = DB::table('product')
            ->join('account_type', 'account_type.id', '=', 'product.account_type_id')
            ->where('product.name', 'opc')
            ->select(
                'product.id',
                'account_type.account as membership',
                'product.price',
                'product.points',
                'product.status'
            )
            ->get();

        return response()->json(['data' => $products]);
    }

    /**
     * Actualiza el precio y puntos del OPC para una membresía específica.
     * Endpoint: PUT /admin/compensation/opc-products/{id}
     */
    public function updateOpcProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price'  => 'required|numeric|min:0',
            'points' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::where('name', 'opc')->findOrFail($id);
        $product->update([
            'price'  => $request->price,
            'points' => $request->points,
        ]);

        return response()->json([
            'message' => 'Producto OPC actualizado exitosamente.',
            'data'    => $product->fresh()
        ]);
    }

    // ==========================================
    // RANGOS (rank_bonus)
    // ==========================================

    /**
     * Lista todos los rangos con sus topes y requisitos.
     * Endpoint: GET /admin/compensation/ranks
     */
    public function getRanks()
    {
        $ranks = RankBonus::orderBy('sort_order')->orderBy('id')->get([
            'id', 'name', 'sort_order', 'vol_min', 'active_direct', 'pack_max',
            'max_pay', 'monthly_bonus', 'monthly_bonus_months', 'monthly_bonus_frequency',
            'extra_bonus', 'limit_generation', 'icon', 'status'
        ]);

        // Cada rango viaja con sus porcentajes generacionales y con el aviso de si
        // está en uso: sin eso, desde el panel no hay forma de saber si se puede
        // borrar o solo retirar.
        $generacionales = DB::table('generational_bonuses')
            ->whereNotNull('rank_bonus_id')
            ->get()
            ->keyBy('rank_bonus_id');

        $conHistorial = DB::table('rank_binary')->distinct()->pluck('rank_id')
            ->merge(DB::table('binary_cut_histories')->distinct()->pluck('rank_id'))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->all();

        $ranks->transform(function ($rank) use ($generacionales, $conHistorial) {
            $rank->generational = $generacionales->get($rank->id);
            $rank->en_uso = in_array((int) $rank->id, $conHistorial, true);

            return $rank;
        });

        return response()->json(['data' => $ranks]);
    }

    /**
     * Actualiza los parámetros de un rango, incluido el nombre.
     * Endpoint: PUT /admin/compensation/ranks/{id}
     *
     * Renombrar es seguro: desde que generational_bonuses tiene su propia columna
     * rank_bonus_id, ni el corte ni el panel dependen ya del nombre para cruzar los
     * porcentajes. El nombre es una etiqueta y nada más.
     */
    public function updateRank(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'                    => 'sometimes|string|max:255|unique:rank_bonus,name,' . $id,
            'sort_order'              => 'nullable|integer|min:0',
            'vol_min'                 => 'nullable|numeric|min:0',
            'active_direct'           => 'nullable|integer|min:0',
            'pack_max'                => 'nullable|integer|min:0',
            'max_pay'                 => 'nullable|numeric|min:0',
            'monthly_bonus'           => 'nullable|numeric|min:0',
            'monthly_bonus_months'    => 'nullable|integer|min:1|max:36',
            'monthly_bonus_frequency' => 'nullable|in:monthly,quarterly',
            'limit_generation'        => 'nullable|integer|min:0|max:8',
            'icon'                    => 'nullable|string|max:255',
            'status'                  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rank = RankBonus::findOrFail($id);

        DB::transaction(function () use ($rank, $request) {
            $rank->update($request->only([
                'name', 'sort_order', 'vol_min', 'active_direct', 'pack_max', 'max_pay',
                'monthly_bonus', 'monthly_bonus_months', 'monthly_bonus_frequency',
                'limit_generation', 'icon', 'status'
            ]));

            // range_name se queda como copia legible del nombre, para que quien mire
            // la tabla a pelo entienda de qué rango es cada fila.
            if ($request->filled('name')) {
                DB::table('generational_bonuses')
                    ->where('rank_bonus_id', $rank->id)
                    ->update(['range_name' => $request->input('name'), 'updated_at' => now()]);
            }
        });

        return response()->json([
            'message' => 'Rango actualizado exitosamente.',
            'data'    => $rank->fresh()
        ]);
    }

    /**
     * Crea un rango nuevo, con su fila de bono generacional.
     * Endpoint: POST /admin/compensation/ranks
     *
     * Las dos filas van juntas siempre: un rango sin porcentajes generacionales no
     * cobraría ese bono y nadie se enteraría hasta el corte siguiente.
     */
    public function storeRank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                    => 'required|string|max:255|unique:rank_bonus,name',
            'sort_order'              => 'nullable|integer|min:0',
            'vol_min'                 => 'required|numeric|min:0',
            'active_direct'           => 'required|integer|min:0',
            'pack_max'                => 'nullable|integer|min:0',
            'max_pay'                 => 'required|numeric|min:0',
            'monthly_bonus'           => 'nullable|numeric|min:0',
            'monthly_bonus_months'    => 'nullable|integer|min:1|max:36',
            'monthly_bonus_frequency' => 'nullable|in:monthly,quarterly',
            'limit_generation'        => 'nullable|integer|min:0|max:8',
            'icon'                    => 'nullable|string|max:255',
            'g_1' => 'nullable|numeric|min:0|max:100',
            'g_2' => 'nullable|numeric|min:0|max:100',
            'g_3' => 'nullable|numeric|min:0|max:100',
            'g_4' => 'nullable|numeric|min:0|max:100',
            'g_5' => 'nullable|numeric|min:0|max:100',
            'g_6' => 'nullable|numeric|min:0|max:100',
            'g_7' => 'nullable|numeric|min:0|max:100',
            'g_8' => 'nullable|numeric|min:0|max:100',
        ]);

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

            DB::table('generational_bonuses')->insert([
                'rank_bonus_id' => $rank->id,
                'range_name'    => $rank->name,
                'g_1' => $request->input('g_1', 0),
                'g_2' => $request->input('g_2', 0),
                'g_3' => $request->input('g_3', 0),
                'g_4' => $request->input('g_4', 0),
                'g_5' => $request->input('g_5', 0),
                'g_6' => $request->input('g_6', 0),
                'g_7' => $request->input('g_7', 0),
                'g_8' => $request->input('g_8', 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $rank;
        });

        return response()->json([
            'message' => 'Rango creado exitosamente.',
            'data'    => $rank->fresh(),
        ], 201);
    }

    /**
     * Retira un rango.
     * Endpoint: DELETE /admin/compensation/ranks/{id}
     *
     * Si el rango se ha llegado a asignar en algún corte no se borra: rank_binary y
     * binary_cut_histories lo referencian con clave foránea, y borrarlo dejaría el
     * historial sin poder explicarse. En ese caso se desactiva, que a efectos del
     * corte es lo mismo —deja de asignarse— sin perder nada.
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
            DB::table('generational_bonuses')->where('rank_bonus_id', $rank->id)->delete();
            $rank->delete();
        });

        return response()->json(['message' => 'Rango eliminado exitosamente.']);
    }

    /**
     * Reordena los rangos de una vez.
     * Endpoint: PUT /admin/compensation/ranks-order
     *
     * El orden importa: el corte recorre los rangos de menor a mayor y se queda con
     * el último que se cumple. Antes salía de vol_min y, en empate, del id, así que
     * un rango nuevo intercalado caía en el sitio equivocado.
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

    // ==========================================
    // BONOS GENERACIONALES (generational_bonuses)
    // ==========================================

    /**
     * Lista los porcentajes de bono generacional por rango.
     * Endpoint: GET /admin/compensation/generational-bonuses
     */
    public function getGenerationalBonuses()
    {
        // Se cruza por rank_bonus_id. Antes se cruzaba por id, y como los
        // identificadores de las dos tablas no se corresponden —el 1 de
        // generational_bonuses es Mentor y el de rank_bonus es Aprendiz—, el panel
        // mostraba los porcentajes de todos los rangos corridos uno.
        $bonuses = DB::table('generational_bonuses')
            ->join('rank_bonus', 'rank_bonus.id', '=', 'generational_bonuses.rank_bonus_id')
            ->select(
                'generational_bonuses.id',
                'generational_bonuses.rank_bonus_id',
                'rank_bonus.name as rank_name',
                'rank_bonus.limit_generation',
                'generational_bonuses.g_1',
                'generational_bonuses.g_2',
                'generational_bonuses.g_3',
                'generational_bonuses.g_4',
                'generational_bonuses.g_5',
                'generational_bonuses.g_6',
                'generational_bonuses.g_7',
                'generational_bonuses.g_8',
            )
            ->orderBy('rank_bonus.sort_order')
            ->orderBy('rank_bonus.id')
            ->get();

        return response()->json(['data' => $bonuses]);
    }

    /**
     * Actualiza los porcentajes generacionales de un rango.
     * Endpoint: PUT /admin/compensation/generational-bonuses/{id}
     */
    public function updateGenerationalBonus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'g_1' => 'nullable|numeric|min:0|max:100',
            'g_2' => 'nullable|numeric|min:0|max:100',
            'g_3' => 'nullable|numeric|min:0|max:100',
            'g_4' => 'nullable|numeric|min:0|max:100',
            'g_5' => 'nullable|numeric|min:0|max:100',
            'g_6' => 'nullable|numeric|min:0|max:100',
            'g_7' => 'nullable|numeric|min:0|max:100',
            'g_8' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('generational_bonuses')->where('id', $id)->update(
            $request->only(['g_1', 'g_2', 'g_3', 'g_4', 'g_5', 'g_6', 'g_7', 'g_8'])
        );

        return response()->json([
            'message' => 'Bonos generacionales actualizados exitosamente.',
            'data'    => DB::table('generational_bonuses')->where('id', $id)->first()
        ]);
    }

    // ==========================================
    // CONTRASTE CON EL DOCUMENTO DEL PLAN
    // ==========================================

    /**
     * Compara la configuración con el documento que se le entrega al afiliado.
     * Endpoint: GET /admin/compensation/verificacion
     *
     * Devuelve, punto por punto, lo que el documento promete y lo que el sistema
     * tiene puesto. El documento de referencia está en config/plan_documento.php:
     * cuando salga el plan nuevo se actualiza ahí y esto vuelve a cuadrar solo.
     */
    public function verification(Request $request, PlanVerificationService $verificacion)
    {
        $resultado = $verificacion->verificar($request->input('seccion'));

        return response()->json(['data' => $resultado]);
    }

    // ==========================================
    // ENDPOINT PÚBLICO: Plan de Membresías
    // ==========================================

    /**
     * Devuelve las membresías activas con su precio para mostrarlo en la UI (landing/registro).
     * Sin autenticación requerida.
     * Endpoint: GET /public/membership-plans
     */
    public function publicMembershipPlans()
    {
        $memberships = AccountType::where('status', 1)
            ->whereIn('account', ['School', 'Academy', 'University', 'Socio Fundador'])
            ->orderBy('price')
            ->get([
                'id', 'account', 'price',
                'fast_cash_bonus', 'pay_in_binary',
                'productor_bonus', 'course_selling_bonus',
                'enrollment_duration'
            ]);

        // Adjuntar precio del OPC a cada membresía
        $memberships->transform(function ($m) {
            $opc = DB::table('product')
                ->where('name', 'opc')
                ->where('account_type_id', $m->id)
                ->first(['price as opc_price', 'points as opc_points']);

            $m->opc_price  = $opc->opc_price ?? 30;
            $m->opc_points = $opc->opc_points ?? 15;
            return $m;
        });

        return response()->json(['data' => $memberships]);
    }
}
