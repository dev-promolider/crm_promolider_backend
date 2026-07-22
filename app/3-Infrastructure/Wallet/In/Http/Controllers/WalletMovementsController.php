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
use Promolider\Application\Wallet\UseCases\GetMyPurchasesUseCase;

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
        private GetMyDirectsUseCase $getMyDirectsUseCase,
        private GetMyPurchasesUseCase $getMyPurchasesUseCase
    ) {}

    public function getAllMovementsWallet(Request $request, int $user_id)
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $dateFrom = $request->input('date_from');
            $dateTo   = $request->input('date_to');
            $status   = $request->input('status');
            $search   = $request->input('search');
            $perPage  = (int) $request->input('per_page', 15);
            $page     = (int) $request->input('page', 1);

            $perPage = max(5, min(100, $perPage)); // clamp 5–100

            $paginator = $this->getAllMovementsWalletUseCase->execute(
                $authUserId,
                $user_id,
                $dateFrom,
                $dateTo,
                $status,
                $search,
                $perPage,
                $page
            );

            return response()->json([
                'success'      => true,
                'data'         => $paginator->items(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ], 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 400 && $code <= 500) ? $code : 500;
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
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

    public function getMyPurchases(Request $request, int $userId)
    {
        try {
            $authUserId = Auth::id();
            if (!$authUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($authUserId !== $userId && !$user->can('withdrawal_funds')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $search  = $request->input('search');
            $perPage = (int) $request->input('per_page', 15);
            $page    = (int) $request->input('page', 1);

            $perPage = max(5, min(100, $perPage));

            $payments = $this->getMyPurchasesUseCase->execute($userId, $search, $perPage, $page);
            return response()->json($payments, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener compras: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveBinaryPoints(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Fetch all users to calculate generations in memory (fast since small DB)
            $allUsers = \Illuminate\Support\Facades\DB::table('users')
                ->select('id', 'id_referrer_sponsor')
                ->get()
                ->keyBy('id')
                ->toArray();

            $points = \Illuminate\Support\Facades\DB::table('points')
                ->join('users', 'points.user_id', '=', 'users.id')
                ->where('points.sponsor_id', $userId)
                ->where('points.status', 1)
                ->select(
                    'points.id',
                    'points.points',
                    'points.side',
                    'points.reason',
                    'points.created_at',
                    'points.user_id as generator_id',
                    'users.name as sponsor_name',
                    'users.last_name as sponsor_last_name',
                    'users.photo as sponsor_photo'
                )
                ->orderBy('points.created_at', 'desc')
                ->get();

            $leftLeg = [];
            $rightLeg = [];
            $totalLeft = 0;
            $totalRight = 0;

            foreach ($points as $point) {
                // Calculate generation
                $generation = -1; // -1 means Spillover (Derrame)
                $currentId = $point->generator_id; // user_id is the buyer
                $steps = 0;

                if ($currentId == $userId) {
                    $generation = 0; // Compra propia
                } else {
                    while (isset($allUsers[$currentId]) && $allUsers[$currentId]->id_referrer_sponsor) {
                        $steps++;
                        $parentId = $allUsers[$currentId]->id_referrer_sponsor;
                        if ($parentId == $userId) {
                            $generation = $steps;
                            break;
                        }
                        $currentId = $parentId;
                        
                        // Prevent infinite loops in bad data
                        if ($steps > 100) break;
                    }
                }

                $pointData = [
                    'id' => $point->id,
                    'points' => (float) $point->points,
                    'reason' => $point->reason,
                    'created_at' => $point->created_at,
                    'sponsor' => [
                        'name' => $point->sponsor_name . ' ' . $point->sponsor_last_name,
                        'photo' => $point->sponsor_photo
                    ],
                    'generation' => $generation
                ];

                if ((int)$point->side === 0) {
                    $leftLeg[] = $pointData;
                    $totalLeft += (float) $point->points;
                } else {
                    $rightLeg[] = $pointData;
                    $totalRight += (float) $point->points;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'left_leg' => $leftLeg,
                    'right_leg' => $rightLeg,
                    'total_left' => $totalLeft,
                    'total_right' => $totalRight
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("[WalletMovementsController] Error getting active binary points: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
