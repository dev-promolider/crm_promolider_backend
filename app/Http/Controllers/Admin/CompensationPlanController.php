<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\Product;
use App\Models\RankBonus;
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
        $ranks = RankBonus::orderBy('id')->get([
            'id', 'name', 'vol_min', 'active_direct',
            'max_pay', 'monthly_bonus', 'limit_generation', 'icon'
        ]);

        return response()->json(['data' => $ranks]);
    }

    /**
     * Actualiza los parámetros de un rango.
     * Endpoint: PUT /admin/compensation/ranks/{id}
     */
    public function updateRank(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vol_min'          => 'nullable|numeric|min:0',
            'active_direct'    => 'nullable|integer|min:0',
            'max_pay'          => 'nullable|numeric|min:0',
            'monthly_bonus'    => 'nullable|numeric|min:0',
            'limit_generation' => 'nullable|integer|min:0|max:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rank = RankBonus::findOrFail($id);
        $rank->update($request->only([
            'vol_min', 'active_direct', 'max_pay',
            'monthly_bonus', 'limit_generation'
        ]));

        return response()->json([
            'message' => 'Rango actualizado exitosamente.',
            'data'    => $rank->fresh()
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
        $bonuses = DB::table('generational_bonuses')
            ->join('rank_bonus', 'rank_bonus.id', '=', 'generational_bonuses.id')
            ->select(
                'generational_bonuses.id',
                'rank_bonus.name as rank_name',
                'generational_bonuses.g_1',
                'generational_bonuses.g_2',
                'generational_bonuses.g_3',
                'generational_bonuses.g_4',
                'generational_bonuses.g_5',
                'generational_bonuses.g_6',
                'generational_bonuses.g_7',
                'generational_bonuses.g_8',
            )
            ->orderBy('generational_bonuses.id')
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
