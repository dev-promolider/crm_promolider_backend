<?php
namespace Promolider\Infrastructure\Dashboard\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Promolider\Application\Dashboard\UseCases\GetTopbarStatsUseCase;
use Promolider\Application\Dashboard\UseCases\GetDashboardWidgetsUseCase;
use Promolider\Application\Dashboard\UseCases\GetUnilevelTreeUseCase;
use Promolider\Application\Dashboard\UseCases\GetBinaryTreeUseCase;

class DashboardController extends Controller
{
    public function __construct(
        private GetTopbarStatsUseCase $getTopbarStatsUseCase,
        private GetDashboardWidgetsUseCase $getDashboardWidgetsUseCase,
        private GetUnilevelTreeUseCase $getUnilevelTreeUseCase
    ) {}

    public function topbarStats()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

        $data = $this->getTopbarStatsUseCase->execute($userId);

        return response()->json([
            'status' => 200,
            'message' => 'Topbar stats retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function getattributes()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

        $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();
        if (!$user) return response()->json(['status' => 404, 'message' => 'User not found'], 404);

        $wallet = \Illuminate\Support\Facades\DB::table('wallet')->where('user_id', $userId)->first();
        $totalPayments = $wallet ? \Illuminate\Support\Facades\DB::table('wallet_movements')
            ->where('wallet_id', $wallet->id)
            ->where('reason', 'LIKE', '%Bono%')
            ->sum('amount') : 0;

        $totalCourses = \Illuminate\Support\Facades\DB::table('courses')->where('user_id', $userId)->count();

        $accountType = \Illuminate\Support\Facades\DB::table('account_type')->where('id', $user->id_account_type)->value('account');

        $totalClients = \Illuminate\Support\Facades\DB::table('classified')->where('user_above', (string)$userId)->count();

        $roleName = \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $userId)
            ->value('roles.name');

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => [
                'totalPayments' => $totalPayments,
                'totalCourses' => $totalCourses,
                'accountType' => $accountType,
                'totalClients' => $totalClients,
                'role' => $roleName ?? 'Student'
            ]
        ], 200);
    }

    public function dashboardWidgets(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

        $timeframe = $request->query('timeframe', 'normal');
        $data = $this->getDashboardWidgetsUseCase->execute($userId, $timeframe);

        return response()->json([
            'status' => 200,
            'message' => 'Dashboard widgets retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function unilevelTree()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

        $data = $this->getUnilevelTreeUseCase->execute($userId);

        return response()->json([
            'status' => 200,
            'message' => 'Unilevel tree retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function binaryTree(GetBinaryTreeUseCase $getBinaryTreeUseCase)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

        $data = $getBinaryTreeUseCase->execute($userId);

        return response()->json([
            'status' => 200,
            'message' => 'Binary tree retrieved successfully',
            'data' => $data
        ], 200);
    }
}
