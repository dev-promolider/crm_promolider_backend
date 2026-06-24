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
        $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();
        
        // 1. Obtener Rango
        $rank = \Illuminate\Support\Facades\DB::table('rank_binary')
            ->join('rank_bonus', 'rank_bonus.id', '=', 'rank_binary.rank_id')
            ->where('rank_binary.user_id', $userId)
            ->orderBy('rank_binary.created_at', 'desc')
            ->select('rank_bonus.*')
            ->first();

        if (!$rank) {
            $rank = \Illuminate\Support\Facades\DB::table('rank_bonus')->first();
        }

        // 2. Obtener Puntos y Nivel
        $points = \Illuminate\Support\Facades\DB::table('user_classroom_points')->where('id_user', $userId)->value('total_points') ?? 0;

        $level = \Illuminate\Support\Facades\DB::table('user_levels')
            ->where('experience_required', '<=', $points)
            ->orderBy('experience_required', 'desc')
            ->first();

        $nextLevel = \Illuminate\Support\Facades\DB::table('user_levels')
            ->where('experience_required', '>', $level ? $level->experience_required : 0)
            ->orderBy('experience_required', 'asc')
            ->first();

        $percentage = 100;
        if ($nextLevel && $nextLevel->experience_required > 0) {
            $percentage = ($points / $nextLevel->experience_required) * 100;
        }

        // 3. Obtener Notificaciones
        $unreadNotifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id_receiver', $userId)
            ->where('seen', 0)
            ->count();
        
        return [
            'credits' => (float) ($user->credits ?? 0),
            'rank' => [
                'name' => $rank->name ?? 'Sin rango',
                'icon' => $rank->icon ?? null,
                'level' => $rank->id ?? 0,
            ],
            'points' => [
                'total' => $points,
                'percentage' => $percentage
            ],
            'notifications' => [
                'unread' => $unreadNotifications
            ]
        ];
    }

    public function getWidgetsData(int $userId): array
    {
        $user = User::find($userId);

        $isMembershipActive = ($user->expiration_membership_date > now()) && ($user->request == 2);
        $isActive = (is_null($user->expiration_date) || $user->expiration_date > now()) && ($user->request == 2);
        
        // Obtener los directos en el árbol binario
        $sponsored = \Illuminate\Support\Facades\DB::table('classified')
            ->join('users', 'classified.user_id', '=', 'users.id')
            ->where('classified.id_user_sponsor', $userId)
            ->select('classified.position', 'users.expiration_date', 'users.expiration_membership_date', 'users.request', 'users.id_account_type')
            ->get();

        $left = false;
        $right = false;

        foreach ($sponsored as $sponsor) {
            $isSponsorMembershipActive = ($sponsor->expiration_membership_date > now()) && ($sponsor->request == 2);
            $isSponsorActive = (is_null($sponsor->expiration_date) || $sponsor->expiration_date > now()) && ($sponsor->request == 2);
            
            if ($isSponsorActive && $isSponsorMembershipActive && $sponsor->id_account_type != 5 && $sponsor->id_account_type != 6) {
                if ($sponsor->position == 0) $left = true;
                if ($sponsor->position == 1) $right = true;
            }
            if ($left && $right) break;
        }

        $isQualified = $left && $right;

        return [
            'conditions' => [
                'membershipActive' => $isMembershipActive,
                'active' => $isActive,
                'qualified' => $isQualified
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
        $rootUser = User::where('id', $userId)->first();

        $directs = User::where('id_referrer_sponsor', $userId)
            ->select(
                'id', 'username', 'name', 'last_name', 'email', 
                'phone', 'date_birth', 'created_at', 'photo', 
                'id_referrer_sponsor', 'id_account_type', 'expiration_membership_date'
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
                'active' => $direct->membershipActive ?? 0,
                'membershipActive' => $direct->membershipActive ?? 0,
                'account_type' => ['id' => $direct->id_account_type, 'account' => 'Socio'] // Hardcoded temporalmente por tabla faltante
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
                'active' => (is_null($rootUser->expiration_date) || $rootUser->expiration_date > now()) && ($rootUser->request == 2),
                'membershipActive' => ($rootUser->expiration_membership_date > now()) && ($rootUser->request == 2),
                'account_type' => ['id' => $rootUser->id_account_type, 'account' => 'Socio']
            ],
            'directs' => $formattedDirects
        ];
    }

    public function getBinaryTree(int $userId): array
    {
        $currentUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();
        if (!$currentUser) return [];

        $data = ['c' => $this->formatBinaryNode($currentUser)];

        $nodeA = $this->findBinaryNode($userId, 0);
        $nodeB = $this->findBinaryNode($userId, 1);

        if ($nodeA) {
            $data['a'] = $this->formatBinaryNode($nodeA);
            $nodeAa = $this->findBinaryNode($nodeA->id, 0);
            if ($nodeAa) $data['aa'] = $this->formatBinaryNode($nodeAa);
            
            $nodeAb = $this->findBinaryNode($nodeA->id, 1);
            if ($nodeAb) $data['ab'] = $this->formatBinaryNode($nodeAb);
        }

        if ($nodeB) {
            $data['b'] = $this->formatBinaryNode($nodeB);
            $nodeBa = $this->findBinaryNode($nodeB->id, 0);
            if ($nodeBa) $data['ba'] = $this->formatBinaryNode($nodeBa);
            
            $nodeBb = $this->findBinaryNode($nodeB->id, 1);
            if ($nodeBb) $data['bb'] = $this->formatBinaryNode($nodeBb);
        }

        return $data;
    }

    private function findBinaryNode(int $sponsorId, int $position)
    {
        $query = "
            WITH RECURSIVE cte AS (
                SELECT id, user_id, user_above, id_user_sponsor, position, 1 as depth
                FROM classified
                WHERE user_above = ? AND position = ?
                
                UNION ALL
                
                SELECT c.id, c.user_id, c.user_above, c.id_user_sponsor, c.position, cte.depth + 1
                FROM classified c
                INNER JOIN cte ON c.user_above = cte.user_id
                WHERE c.position = ?
            )
            SELECT user_id FROM cte WHERE id_user_sponsor = ? ORDER BY depth ASC LIMIT 1
        ";

        $result = \Illuminate\Support\Facades\DB::selectOne($query, [$sponsorId, $position, $position, $sponsorId]);

        if ($result) {
            return \Illuminate\Support\Facades\DB::table('users')->where('id', $result->user_id)->first();
        }
        return null;
    }

    private function formatBinaryNode($user)
    {
        if (!$user) return null;
        
        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => trim($user->name . ' ' . $user->last_name),
            'first_name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'photo' => $user->photo,
            'membershipActive' => ($user->expiration_membership_date > now()) && ($user->request == 2),
            'active' => (is_null($user->expiration_date) || $user->expiration_date > now()) && ($user->request == 2),
            'qualified' => 1,
            'LeftPoints' => 0,  // TODO: Implement points query
            'RightPoints' => 0, // TODO: Implement points query
            'account_type' => ['id' => $user->id_account_type, 'account' => 'Socio']
        ];
    }
}
