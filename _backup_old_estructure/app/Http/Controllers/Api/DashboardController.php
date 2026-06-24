<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RankService;
use App\Services\UserLevelService;
use App\Services\NotificationService;

class DashboardController extends Controller
{
    public function topbarStats(RankService $rankService, UserLevelService $levelService, NotificationService $notificationService)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $credits = $user->credits ?? 0;
        $rank = $rankService->myRank();
        $points = $levelService->myPoints();
        $pointsPercentage = $levelService->porcentaje();
        $unreadNotifications = $notificationService->countNotification();

        return response()->json([
            'status' => 200,
            'message' => 'Topbar stats retrieved successfully',
            'data' => [
                'credits' => (float) $credits,
                'rank' => [
                    'name' => $rank->name ?? 'Sin rango',
                    'icon' => $rank->icon ?? null,
                    'level' => $rank->id ?? 0,
                ],
                'points' => [
                    'total' => $points,
                    'percentage' => $pointsPercentage
                ],
                'notifications' => [
                    'unread' => $unreadNotifications
                ]
            ]
        ], 200);
    }

    public function dashboardWidgets()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // Estas estadísticas pueden ir expandiéndose en el futuro mediante queries reales a la DB
        return response()->json([
            'status' => 200,
            'message' => 'Dashboard widgets retrieved successfully',
            'data' => [
                'conditions' => [
                    'membershipActive' => $user->membershipActive,
                    'active' => $user->active,
                    'qualified' => $user->qualified
                ],
                'monthly_bonuses' => [
                    'expansion' => 0.00,
                    'binary' => 0.00,
                    'generational' => 0.00
                ],
                'cumulative_bonuses' => [
                    'fast_cash' => 0.00,
                    'producer' => 0.00,
                    'course_sale' => 0.00
                ]
            ]
        ], 200);
    }

    public function unilevelTree()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // Cargar los datos del usuario raíz y su tipo de cuenta en una sola consulta con JOIN
        $rootUser = \App\Models\User::join('account_types', 'users.id_account_type', '=', 'account_types.id')
            ->where('users.id', $user->id)
            ->select('users.*', 'account_types.account as account_type_name')
            ->first();

        // Obtener los referidos directos (Árbol Uninivel) con una sola consulta SQL usando JOIN en lugar de eager loading
        $directs = \App\Models\User::join('account_types', 'users.id_account_type', '=', 'account_types.id')
            ->where('users.id_referrer_sponsor', $user->id)
            ->select(
                'users.id', 'users.username', 'users.name', 'users.last_name', 'users.email', 
                'users.phone', 'users.date_birth', 'users.created_at', 'users.photo', 
                'users.id_referrer_sponsor', 'users.id_account_type', 'users.expiration_membership_date',
                'account_types.account as account_type_name'
            )
            ->get();

        // Formateamos la data
        $formattedDirects = $directs->map(function ($direct) {
            return [
                'id' => $direct->id,
                'username' => $direct->username,
                'name' => trim($direct->name . ' ' . $direct->last_name),
                'first_name' => $direct->name,
                'last_name' => $direct->last_name,
                'email' => $direct->email,
                'phone' => $direct->phone,
                'date_birth' => $direct->date_birth,
                'created_at' => $direct->created_at,
                'photo' => $direct->photo,
                'active' => $direct->membershipActive, // Usa el accesor del modelo
                'membershipActive' => $direct->membershipActive,
                'account_type' => ['id' => $direct->id_account_type, 'account' => $direct->account_type_name]
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Unilevel tree retrieved successfully',
            'data' => [
                'root' => [
                    'id' => $rootUser->id,
                    'username' => $rootUser->username,
                    'name' => trim($rootUser->name . ' ' . $rootUser->last_name),
                    'first_name' => $rootUser->name,
                    'last_name' => $rootUser->last_name,
                    'email' => $rootUser->email,
                    'phone' => $rootUser->phone,
                    'date_birth' => $rootUser->date_birth,
                    'created_at' => $rootUser->created_at,
                    'photo' => $rootUser->photo,
                    'active' => $rootUser->membershipActive,
                    'membershipActive' => $rootUser->membershipActive,
                    'account_type' => ['id' => $rootUser->id_account_type, 'account' => $rootUser->account_type_name]
                ],
                'directs' => $formattedDirects
            ]
        ], 200);
    }
}
