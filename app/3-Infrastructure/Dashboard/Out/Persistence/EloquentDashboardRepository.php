<?php
namespace Promolider\Infrastructure\Dashboard\Out\Persistence;

use Promolider\Domain\Dashboard\Ports\Out\DashboardRepositoryInterface;
use App\Models\User;
use App\Services\RankService;
use App\Services\UserLevelService;
use App\Services\NotificationService;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function getTopbarStats(int $userId): array
    {
        $user = User::find($userId);
        
        // Asumiendo que estos servicios heredados siguen existiendo o se inyectarán
        // En una refactorización completa, reescribiríamos las queries de estos servicios aquí mismo.
        $rankService = app(RankService::class);
        $levelService = app(UserLevelService::class);
        $notificationService = app(NotificationService::class);

        $rank = $rankService->myRank();
        
        return [
            'credits' => (float) ($user->credits ?? 0),
            'rank' => [
                'name' => $rank->name ?? 'Sin rango',
                'icon' => $rank->icon ?? null,
                'level' => $rank->id ?? 0,
            ],
            'points' => [
                'total' => $levelService->myPoints(),
                'percentage' => $levelService->porcentaje()
            ],
            'notifications' => [
                'unread' => $notificationService->countNotification()
            ]
        ];
    }

    public function getWidgetsData(int $userId): array
    {
        $user = User::find($userId);

        return [
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
        ];
    }

    public function getUnilevelTree(int $userId): array
    {
        $rootUser = User::join('account_types', 'users.id_account_type', '=', 'account_types.id')
            ->where('users.id', $userId)
            ->select('users.*', 'account_types.account as account_type_name')
            ->first();

        $directs = User::join('account_types', 'users.id_account_type', '=', 'account_types.id')
            ->where('users.id_referrer_sponsor', $userId)
            ->select(
                'users.id', 'users.username', 'users.name', 'users.last_name', 'users.email', 
                'users.phone', 'users.date_birth', 'users.created_at', 'users.photo', 
                'users.id_referrer_sponsor', 'users.id_account_type', 'users.expiration_membership_date',
                'account_types.account as account_type_name'
            )
            ->get();

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
                'active' => $direct->membershipActive,
                'membershipActive' => $direct->membershipActive,
                'account_type' => ['id' => $direct->id_account_type, 'account' => $direct->account_type_name]
            ];
        });

        return [
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
        ];
    }
}
