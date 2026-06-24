<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Product;

class OPCController extends Controller
{
    public function index()
    {
        try {
            $opc = Product::where('name', 'opc')->get();

            if ($opc->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se encontraron configuraciones para OPC',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $opc,
                'message' => 'Data recuperada con éxito',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Ocurrió un error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $opc = Product::where('name', 'opc')->first();

            $opc->descripcion = $request->input('descripcion');
            $opc->price = $request->input('price');
            $opc->promotion_prince = $request->input('promotion_prince');
            $opc->commission = $request->input('commission');
            $opc->points = $request->input('points');

            $opc->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Actualización exitosa.',
                'data' => $opc
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar.',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCurrentPrice()
    {
        try {
            $opcProduct = Product::where('name', 'opc')->first();

            if (!$opcProduct) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se encontró configuración de OPC',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'price' => $opcProduct->price,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Ocurrió un error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getExpirationDate()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            return response()->json([
                'status' => true,
                'expiration_date' => $user->expiration_date
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Ocurrió un error: ' . $th->getMessage()
            ], 500);
        }
    }
}
