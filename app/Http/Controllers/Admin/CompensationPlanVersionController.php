<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MLM\PlanVersionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Versiones guardadas del plan: guardar una foto, compararla con lo actual y
 * volver a ella.
 */
class CompensationPlanVersionController extends Controller
{
    public function __construct(private PlanVersionService $versiones)
    {
    }

    /**
     * Endpoint: GET /admin/compensation/versions
     */
    public function index()
    {
        $lista = DB::table('compensation_plan_versions as v')
            ->leftJoin('users as u', 'u.id', '=', 'v.created_by')
            ->orderByDesc('v.created_at')
            ->orderByDesc('v.id')
            ->get(['v.id', 'v.name', 'v.notes', 'v.is_automatic', 'v.created_at', 'u.username as creada_por'])
            ->map(function ($v) {
                $v->is_automatic = (bool) $v->is_automatic;
                return $v;
            });

        return response()->json(['data' => $lista]);
    }

    /**
     * Endpoint: POST /admin/compensation/versions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:150',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id = $this->versiones->guardar(
            $request->input('name'),
            $request->input('notes'),
            optional($request->user())->id
        );

        return response()->json([
            'message' => 'Versión del plan guardada.',
            'data'    => ['id' => $id],
        ], 201);
    }

    /**
     * Qué ha cambiado desde esa versión.
     * Endpoint: GET /admin/compensation/versions/{id}
     */
    public function show(int $id)
    {
        $version = DB::table('compensation_plan_versions')->find($id, ['id', 'name', 'notes', 'is_automatic', 'created_at']);

        if (!$version) {
            return response()->json(['message' => 'Versión no encontrada.'], 404);
        }

        try {
            $diferencias = $this->versiones->comparar($id);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'version'     => $version,
                'diferencias' => $diferencias,
                'total'       => count($diferencias),
            ],
        ]);
    }

    /**
     * Endpoint: POST /admin/compensation/versions/{id}/restore
     */
    public function restore(Request $request, int $id)
    {
        if (!$request->boolean('confirmar')) {
            return response()->json(['message' => 'Restaurar una versión cambia lo que el sistema paga: hay que confirmarlo expresamente.'], 422);
        }

        try {
            $informe = $this->versiones->restaurar($id, optional($request->user())->id);
        } catch (\RuntimeException $e) {
            $codigo = in_array($e->getCode(), [404, 422], true) ? $e->getCode() : 500;

            return response()->json(['message' => $e->getMessage()], $codigo);
        }

        return response()->json([
            'message' => 'Plan restaurado. Se guardó una copia de lo que había antes, por si hay que deshacerlo.',
            'data'    => $informe,
        ]);
    }

    /**
     * Endpoint: DELETE /admin/compensation/versions/{id}
     */
    public function destroy(int $id)
    {
        $borradas = DB::table('compensation_plan_versions')->where('id', $id)->delete();

        return $borradas
            ? response()->json(['message' => 'Versión eliminada.'])
            : response()->json(['message' => 'Versión no encontrada.'], 404);
    }
}
