<?php

namespace App\Http\Controllers;

use App\Models\RankBonus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RankBonusController extends Controller
{
    public function index()
    {
        try {
            $rankBonus = RankBonus::get();

            return response()->json([
                'status' => true,
                'data' => $rankBonus,
                'message' => 'Data recuperada con exito',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Ocurrio un error ' . $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $id = $request->input('id');

            $rankBonus = RankBonus::firstOrNew(['id' => $id]);

            $rankBonus->name = $request->input('name');
            $rankBonus->vol_min = $request->input('vol_min');
            $rankBonus->pack_max = $request->input('pack_max');
            $rankBonus->active_direct = $request->input('active_direct');
            $rankBonus->max_pay = $request->input('max_pay');
            $rankBonus->monthly_bonus = $request->input('monthly_bonus');

            $rankBonus->save();

            return response()->json([
                'status' => 'success',
                'message' => $id ? 'Registro actualizado' : 'Registro exitoso',
                'data' => $rankBonus
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $id ? 'Error al actualizar' : 'Error al registar',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
