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
        private InitRechargeOpenpayPaymentUseCase $initRechargeUseCase
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
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }
}
