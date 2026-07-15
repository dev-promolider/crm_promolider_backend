<?php

namespace Promolider\Infrastructure\Wallet\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Wallet\UseCases\GetAllMovementsWalletUseCase;
use Promolider\Application\Wallet\UseCases\GetWalletBalanceUseCase;
use Promolider\Application\Wallet\UseCases\GetAllMovementsHistoryWalletUseCase;
use Promolider\Application\Wallet\UseCases\TransferFundsUseCase;
use Promolider\Application\Wallet\UseCases\RequestFundsUseCase;
use Promolider\Application\Wallet\UseCases\RequestFundsListUseCase;
use Promolider\Application\Wallet\UseCases\RejectRequestUseCase;
use Promolider\Application\Wallet\UseCases\ApproveRequestUseCase;
use Promolider\Application\Wallet\UseCases\GetBinaryHistoryUseCase;
use Promolider\Application\Wallet\UseCases\GetSalesUseCase;
use Promolider\Application\Wallet\UseCases\GetMyDirectsUseCase;

class WalletMovementsController extends Controller
{
    public function __construct(
        private GetAllMovementsWalletUseCase $getAllMovementsWalletUseCase,
        private GetWalletBalanceUseCase $getWalletBalanceUseCase,
        private GetAllMovementsHistoryWalletUseCase $getAllMovementsHistoryWalletUseCase,
        private TransferFundsUseCase $transferFundsUseCase,
        private RequestFundsUseCase $requestFundsUseCase,
        private RequestFundsListUseCase $requestFundsListUseCase,
        private RejectRequestUseCase $rejectRequestUseCase,
        private ApproveRequestUseCase $approveRequestUseCase,
        private GetBinaryHistoryUseCase $getBinaryHistoryUseCase,
        private GetSalesUseCase $getSalesUseCase,
        private GetMyDirectsUseCase $getMyDirectsUseCase
    ) {}

    public function getAllMovementsWallet(int $user_id)
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $movements = $this->getAllMovementsWalletUseCase->execute($authUserId, $user_id);
            return response()->json([
                'success' => true,
                'data' => $movements
            ], 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code <= 500) ? $code : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getWalletBalance()
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $balanceData = $this->getWalletBalanceUseCase->execute($authUserId);
            return response()->json($balanceData, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code <= 500) ? $code : 500;
            return response()->json([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getAllMovementsHistoryWallet()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $history = $this->getAllMovementsHistoryWalletUseCase->execute();
            return response()->json($history, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function transferFounds(Request $request)
    {
        $request->validate([
            'direct' => 'required|integer',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $result = $this->transferFundsUseCase->execute(
                $authUserId,
                (int) $request->direct,
                (float) $request->amount
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code <= 500) ? $code : 500;
            return response()->json([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function requestFounds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'account_type' => 'required|string',
            'account_number' => 'required|string',
        ]);

        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $result = $this->requestFundsUseCase->execute(
                $authUserId,
                (float) $request->amount,
                $request->account_type,
                $request->account_number
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code <= 500) ? $code : 500;
            return response()->json([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function requestFoundsList()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $list = $this->requestFundsListUseCase->execute();
            return response()->json($list, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function rejectRequest(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $this->rejectRequestUseCase->execute((int)$request->id);
            return response()->json([
                'message' => 'Solicitud rechazada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function approveRequest(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'support_image' => 'nullable|file|image|max:10240',
            'message' => 'nullable|string'
        ]);

        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $imageFile = $request->hasFile('support_image') ? $request->file('support_image') : null;

            $this->approveRequestUseCase->execute(
                (int)$request->id,
                $request->message,
                $imageFile
            );

            return response()->json([
                'message' => 'Solicitud aprobada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al aprobar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBinaryHistory(Request $request)
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $search = $request->input('search');
            $sortKey = $request->input('sort_key', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $perPage = (int)$request->input('per_page', 10);

            $history = $this->getBinaryHistoryUseCase->execute(
                $authUserId,
                $search,
                $sortKey,
                $sortOrder,
                $perPage
            );

            return response()->json($history, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el historial binario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSales(int $id)
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $sales = $this->getSalesUseCase->execute($authUserId, $id);
            return response()->json([
                'success' => true,
                'data' => $sales,
                'message' => 'Data recuperada con éxito'
            ], 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code <= 500) ? $code : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getMyDirects()
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $directs = $this->getMyDirectsUseCase->execute($authUserId);
            return response()->json($directs, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener referidos directos: ' . $e->getMessage()
            ], 500);
        }
    }
}
