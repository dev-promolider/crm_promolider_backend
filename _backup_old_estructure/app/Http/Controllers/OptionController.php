<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\OpenpayOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptionController extends Controller
{

    public function show()
    {
        $options = DB::table('options')->get();
        return view('content.config.config-option')->with('options', $options);
    }

    public function values()
    {
        $options = DB::table('options')->get();
        return $options;
    }

    public function edit(Option $option)
    {
        //
    }

    public function update(Request $request)
    {
        if ($request->v1 == 1) {
            $this->updateOptions('default_avatar', $request->default_avatar);
        }

        if ($request->v2 == 1) {
            $this->updateOptions('daily_question', $request->daily_question);
        }

        if ($request->v3 == 1) {
            $this->updateOptions('achievement', $request->achievement);
        }

        if ($request->v4 == 1) {
            $this->updateOptions('badges_level_one', $request->badges_level_one);
        }

        if ($request->v5 == 1) {
            $this->updateOptions('badges_level_two', $request->badges_level_two);
        }

        if ($request->v6 == 1) {
            $this->updateOptions('badges_level_three', $request->badges_level_three);
        }

        if ($request->v7 == 1) {
            $this->updateOptions('currency_value', $request->money_points);
        }

        if ($request->v8 == 1) {
            Log::info('Updating IVA rate to:', ['value' => $request->iva_rate]);
            $this->updateOptions('iva_rate', $request->iva_rate);
        }
    }

    public function updateOptions($description, $value)
    {

        try {
            DB::beginTransaction();

            $course = Option::where('description', $description)->get()->first();
            $course->value = $value;

            if ($course->update()) {
                $response['status'] = 'ok';
            } else {
                $response['status'] = 'error';
            }
            echo json_encode($response);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function openpayOrder(Request $request = null)
    {
        try {
            Log::info('🟦 Iniciando openpayOrder', ['ip' => $request?->ip(), 'input' => $request?->all()]);
            
            $order = OpenpayOrder::first();
            if (!$order) {
                Log::warning('⚠️ No se encontró registro de orden en la base de datos');
                return response()->json(['status' => false, 'message' => 'No se encontró registro de orden.'], 404);
            }
        
            $openpay_order = (int) $order->value;
            $new_order = $openpay_order + 1;
            $order->value = $new_order;
            $order->update();
        
            Log::info("📦 Orden generada: {$openpay_order} → nuevo valor: {$new_order}");
        
            return response()->json([
                'status' => true,
                'message' => 'Orden generada correctamente',
                'data' => [
                    'openpay_order' => $openpay_order,
                    'new_order' => $new_order,
                ]
            ], 201);
        
        } catch (\Throwable $th) {
            Log::error('❌ Error en openpayOrder', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error generando orden'
            ], 500);
        }
    }

    /**
     * Genera el siguiente número de orden (uso interno)
     * @return int El número de orden generado
     */
    public function generateOrderNumber()
    {
        try {
            Log::info('🟦 Generando número de orden interno');
            
            $order = OpenpayOrder::first();
            if (!$order) {
                Log::error('⚠️ No se encontró registro de orden en la base de datos');
                throw new \Exception('No se encontró registro de orden');
            }
        
            $openpay_order = (int) $order->value;
            $new_order = $openpay_order + 1;
            $order->value = $new_order;
            $order->save();
        
            Log::info("📦 Orden generada: {$openpay_order} → nuevo valor: {$new_order}");
        
            // ✅ RETORNAR SOLO EL NÚMERO
            return $openpay_order;
        
        } catch (\Throwable $th) {
            Log::error('❌ Error generando número de orden', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

}
