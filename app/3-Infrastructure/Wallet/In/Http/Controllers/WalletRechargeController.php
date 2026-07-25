<?php
namespace Promolider\Infrastructure\Wallet\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Promolider\Application\Wallet\UseCases\InitRechargeOpenpayPaymentUseCase;
use Exception;

class WalletRechargeController extends Controller
{
    public function __construct(
        private InitRechargeOpenpayPaymentUseCase $initRechargeUseCase,
        private \Promolider\Application\Wallet\UseCases\ConfirmRechargeOpenpayPaymentUseCase $confirmRechargeUseCase
    ) {}

    /**
     * POST /api/wallet/recharge/openpay
     */
    public function openpayRecharge(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $result = $this->initRechargeUseCase->execute($userId, (float) $request->input('amount'));

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            $code = $e->getCode();
            $statusCode = (is_numeric($code) && $code >= 400 && $code < 600) ? (int)$code : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * POST /api/wallet/recharge/confirm-openpay
     */
    public function confirmOpenpayRecharge(Request $request)
    {
        $request->validate([
            'id' => 'required|string', // Este es el charge_id de Openpay
        ]);

        try {
            $result = $this->confirmRechargeUseCase->execute($request->input('id'));

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            $code = $e->getCode();
            $statusCode = (is_numeric($code) && $code >= 400 && $code < 600) ? (int)$code : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }
}
