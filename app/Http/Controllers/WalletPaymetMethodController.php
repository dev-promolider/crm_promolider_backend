<?php

namespace App\Http\Controllers;

use App\Models\WalletPaymetMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WalletPaymetMethodController extends Controller
{
    /**
     * Obtiene todos los métodos de pago disponibles.
     */
    public function getAll()
    {
        // Obtener todos los registros de WalletPaymentMethod
        $paymentMethods = WalletPaymetMethod::all();

        // Retornar como respuesta JSON
        return response()->json([
            'data' => $paymentMethods
        ], 200);
    }

    /**
     * Asocia un método de pago al usuario autenticado.
     */
    public function config(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            $walletPaymentMethodId = $request->method_id;

            $additionalData = [
                'email'          => $request->_email,
                'account_number' => $request->account_number,
                // Puedes agregar más campos, por ejemplo, 'account_name', si lo necesitas
            ];

            // Opcional: Log para ver los datos recibidos
            Log::info('Asociando método de pago', [
                'user_id' => $user->id,
                'method_id' => $walletPaymentMethodId,
                'additionalData' => $additionalData,
            ]);

            // Se asume que ya tienes definida la relación en el modelo User
            $user->walletPaymentMethods()->attach($walletPaymentMethodId, $additionalData);

            return response()->json(['message' => 'Método de pago asociado correctamente.'], 200);
        }

        return response()->json(['message' => 'Usuario no autenticado.'], 401);
    }

    /**
     * Obtiene las cuentas de métodos de pago asociados al usuario autenticado.
     */
    public function getUserWalletAccounts(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            // Se asume que la relación está definida en el modelo User
            $accounts = $user->walletPaymentMethods()->get();

            return response()->json([
                'accounts' => $accounts
            ], 200);
        }

        return response()->json(['message' => 'Usuario no autenticado.'], 401);
    }

    // Métodos de recurso que puedes implementar según necesites:
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(WalletPaymetMethod $walletPaymetMethod)
    {
        //
    }

    public function edit(WalletPaymetMethod $walletPaymetMethod)
    {
        //
    }

    public function update(Request $request, WalletPaymetMethod $walletPaymetMethod)
    {
        //
    }

    public function destroy(WalletPaymetMethod $walletPaymetMethod)
    {
        //
    }
}
