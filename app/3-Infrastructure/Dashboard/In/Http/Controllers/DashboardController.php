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

    public function dashboardWidgets()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);

        $data = $this->getDashboardWidgetsUseCase->execute($userId);

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
