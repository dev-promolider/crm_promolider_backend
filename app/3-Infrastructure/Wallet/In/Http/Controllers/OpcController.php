<?php

namespace Promolider\Infrastructure\Wallet\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Promolider\Application\Wallet\UseCases\OPC\InitOpcOpenpayPaymentUseCase;
use Promolider\Application\Wallet\UseCases\OPC\ConfirmOpcOpenpayPaymentUseCase;
use Promolider\Application\Wallet\UseCases\OPC\PurchaseOpcWithWalletUseCase;
use Illuminate\Support\Facades\Log;

class OpcController extends Controller
{
    public function __construct(
        private InitOpcOpenpayPaymentUseCase $initOpcUseCase,
        private ConfirmOpcOpenpayPaymentUseCase $confirmOpcUseCase,
        private PurchaseOpcWithWalletUseCase $purchaseOpcWithWalletUseCase
    ) {}

    /**
     * Inicia el proceso de pago con Openpay para la recompra de OPC.
     */
    public function initPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'cuotas' => 'required|integer|min:1'
            ]);

            $userId = auth()->id();
            
            $result = $this->initOpcUseCase->execute($userId, $validated['cuotas']);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        }
    }

    /**
     * Confirma el pago después de que Openpay procesa el 3D Secure (vía redirect o webhook).
     */
    public function confirmPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'charge_id' => 'required|string'
            ]);

            $result = $this->confirmOpcUseCase->execute($validated['charge_id']);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            Log::error("Error en confirmación de OPC: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        }
    }

    /**
     * Procesa el pago de OPC con saldo de Billetera.
     */
    public function purchaseWithWallet(Request $request)
    {
        try {
            $validated = $request->validate([
                'cuotas' => 'required|integer|min:1'
            ]);

            $userId = auth()->id();
            
            $result = $this->purchaseOpcWithWalletUseCase->execute($userId, $validated['cuotas']);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            Log::error("Error en pago OPC con billetera: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        }
    }
}
