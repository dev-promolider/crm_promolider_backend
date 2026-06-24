<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\GenerationalBonus;


class GenerationalBonusController extends Controller
{
     public function index()
    {
        try {
            $generationalBonus = GenerationalBonus::get();

            return response()->json([
                'status' => true,
                'data' => $generationalBonus,
                'message' => 'Data recuperada con exito',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Ocurrio un error! ' . $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
    try {
        $id = $request->input('id');
        
        // Busca el registro por su ID o lanza una excepción si no se encuentra
        $generationalBonus = GenerationalBonus::findOrFail($id);

        // Actualiza los campos con los datos enviados desde el frontend
        $generationalBonus->g_1 = $request->input('g_1');
        $generationalBonus->g_2 = $request->input('g_2');
        $generationalBonus->g_3 = $request->input('g_3');
        $generationalBonus->g_4 = $request->input('g_4');
        $generationalBonus->g_5 = $request->input('g_5');
        $generationalBonus->g_6 = $request->input('g_6');
        $generationalBonus->g_7 = $request->input('g_7');
        $generationalBonus->g_8 = $request->input('g_8');

        // Guarda los cambios en la base de datos
        $generationalBonus->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Registro actualizado correctamente',
            'data' => $generationalBonus
        ], Response::HTTP_OK);
        
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al actualizar el registro',
            'error' => $th->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   
}
