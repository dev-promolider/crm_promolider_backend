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
        
        // 1. Obtener Rango (Mock temporal hasta migrar rangos binarios)
        $rank = \Illuminate\Support\Facades\DB::table('rank_bonus')->first();

        // 2. Obtener Puntos y Nivel (Mock temporal hasta migrar gamificación)
        $points = 0;
        $percentage = 0;

        // 3. Obtener Notificaciones (Mock temporal hasta migrar notificaciones)
        $unreadNotifications = 0;
        
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

    public function getWidgetsData(int $userId, string $timeframe = 'normal'): array
    {
        $user = User::find($userId);

        $isMembershipActive = true; // Mock temporal hasta unir con account_type_details
        $isActive = $user->is_approved == 1;
        
        // Obtener los hijos inmediatos en el árbol binario (las dos patas)
        $sponsored = \Illuminate\Support\Facades\DB::table('binary_tree')
            ->join('users', 'binary_tree.user_id', '=', 'users.id')
            ->leftJoin('account_type_details', function ($join) {
                $join->on('account_type_details.user_id', '=', 'users.id')
                     ->where('account_type_details.status', 1)
                     ->where('account_type_details.expiration_date', '>', now());
            })
            ->where('binary_tree.user_above', $userId)
            ->select('binary_tree.position', 'account_type_details.id as has_active_membership', 'users.is_approved')
            ->get();

        $left = false;
        $right = false;

        foreach ($sponsored as $sponsor) {
            $isSponsorActive = $sponsor->is_approved == 1;
            
            if ($isSponsorActive && $sponsor->has_active_membership) {
                if ($sponsor->position === 'L') $left = true;
                if ($sponsor->position === 'R') $right = true;
            }
            if ($left && $right) break;
        }

        $isQualified = $left && $right;

        $wallet = \Illuminate\Support\Facades\DB::table('wallet')->where('id_user', $userId)->first();
        $walletId = $wallet ? $wallet->id : 0;

        $thisMonth = $timeframe === 'historical' ? null : now()->startOfMonth();

        $monthlyQuery = function ($reasonQuery) use ($walletId, $thisMonth) {
            $query = \Illuminate\Support\Facades\DB::table('wallet_movements')
                ->where('wallet_id', $walletId)
                ->where($reasonQuery);
            if ($thisMonth) {
                $query->where('created_at', '>=', $thisMonth);
            }
            return $query;
        };

        $expansionMonthly = $monthlyQuery(function ($query) {
            $query->where('reason', 'LIKE', '%Bono de expansión%');
        })->sum('amount');

        $binaryMonthly = $monthlyQuery(function ($query) {
            $query->where('reason', 'LIKE', '%Bono binario%');
        })->sum('amount');

        $generationalMonthly = $monthlyQuery(function ($query) {
            $query->where('reason', 'LIKE', 'Bono de % Generación%');
        })->sum('amount');

        // Obtener la fecha del último corte binario para este usuario
        $lastCutDate = null;
        if ($timeframe !== 'historical') {
            $lastCutDate = \Illuminate\Support\Facades\DB::table('binary_cut_histories')
                ->where('user_id', $userId)
                ->max('created_at');
        }

        // Construir la consulta base para acumulativos
        $cumulativeQuery = function ($reasonQuery) use ($walletId, $lastCutDate) {
            $query = \Illuminate\Support\Facades\DB::table('wallet_movements')
                ->where('wallet_id', $walletId)
                ->where($reasonQuery);
            if ($lastCutDate) {
                $query->where('created_at', '>=', $lastCutDate);
            }
            return $query;
        };

        $fastCashCumulative = $cumulativeQuery(function ($query) {
            $query->where('reason', 'LIKE', '%Bono de efectivo rápido%')
                  ->orWhere('reason', 'LIKE', '%Bono de efectivo rapido%');
        })->sum('amount');

        $producerCumulative = $cumulativeQuery(function ($query) {
            $query->where('reason', 'LIKE', '%Bono de productor%');
        })->sum('amount');

        $courseSaleCumulative = $cumulativeQuery(function ($query) {
            $query->where('reason', 'LIKE', '%Bono por compra de curso%');
        })->sum('amount');

        return [
            'conditions' => [
                'membershipActive' => $isMembershipActive,
                'active' => $isActive,
                'qualified' => $isQualified
            ],
            'last_cut_date' => $lastCutDate ? \Carbon\Carbon::parse($lastCutDate)->format('d/m/Y, H:i') : null,
            'monthly_bonuses' => [
                'expansion' => round((float)$expansionMonthly, 2),
                'binary' => round((float)$binaryMonthly, 2),
                'generational' => round((float)$generationalMonthly, 2)
            ],
            'cumulative_bonuses' => [
                'fast_cash' => round((float)$fastCashCumulative, 2),
                'producer' => round((float)$producerCumulative, 2),
                'course_sale' => round((float)$courseSaleCumulative, 2)
            ]
        ];
    }

    public function getUnilevelTree(int $userId): array
    {
        $allUsers = User::select(
                'id', 'username', 'name', 'last_name', 'email', 
                'phone', 'date_birth', 'created_at', 'photo', 
                'id_referrer_sponsor', 'is_approved'
            )->get();

        $rootUser = $allUsers->firstWhere('id', $userId);
        if (!$rootUser) return [];

        $childrenMap = [];
        foreach ($allUsers as $u) {
            $sponsorId = $u->id_referrer_sponsor;
            if ($sponsorId) {
                if (!isset($childrenMap[$sponsorId])) {
                    $childrenMap[$sponsorId] = [];
                }
                $childrenMap[$sponsorId][] = $u;
            }
        }

        $classifications = \Illuminate\Support\Facades\DB::table('binary_tree')->get()->keyBy('user_id')->toArray();

        $buildTree = function($currentUser, $depth = 1) use (&$buildTree, &$childrenMap, $classifications, $userId) {
            $children = $childrenMap[$currentUser->id] ?? [];
            $formattedDirects = [];
            
            foreach ($children as $child) {
                $leg = 'none';
                $currentId = $child->id;
                
                while (isset($classifications[$currentId]) && $classifications[$currentId]->user_above) {
                    $parentId = (int) $classifications[$currentId]->user_above;
                    $position = $classifications[$currentId]->position; // 'L' o 'R'
                    
                    if ($parentId === $userId) {
                        $leg = ($position === 'L') ? 'Izquierda' : 'Derecha';
                        break;
                    }
                    $currentId = $parentId;
                    
                    if ($currentId === $child->id) break;
                }

                $membershipActive = 1;

                $childData = [
                    'id' => $child->id,
                    'username' => $child->username,
                    'name' => trim($child->name . ' ' . $child->last_name),
                    'first_name' => $child->name,
                    'last_name' => $child->last_name,
                    'email' => $child->email,
                    'phone' => $child->phone,
                    'date_birth' => $child->date_birth,
                    'created_at' => $child->created_at,
                    'photo' => $child->photo,
                    'photoUrl' => !empty($child->photo) ? \App\Helpers\ParseUrl::contacAtrrS3($child->photo) : null,
                    'active' => $child->is_approved == 1 ? 1 : 0,
                    'membershipActive' => $membershipActive,
                    'leg' => $leg,
                    'generation' => $depth,
                    'account_type' => ['id' => 1, 'account' => 'Socio']
                ];
                
                $childData['directs'] = $buildTree($child, $depth + 1);
                $formattedDirects[] = $childData;
            }
            
            return $formattedDirects;
        };

        $treeData = $buildTree($rootUser, 1);

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
                'active' => $rootUser->is_approved == 1,
                'membershipActive' => true,
                'account_type' => ['id' => 1, 'account' => 'Socio']
            ],
            'directs' => $treeData
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
        $positionStr = $position === 0 ? 'L' : 'R';
        
        $query = "
            WITH RECURSIVE cte AS (
                SELECT id, user_id, user_above, binary_sponsor, position, 1 as depth
                FROM binary_tree
                WHERE user_above = ? AND position = ?
                
                UNION ALL
                
                SELECT c.id, c.user_id, c.user_above, c.binary_sponsor, c.position, cte.depth + 1
                FROM binary_tree c
                INNER JOIN cte ON c.user_above = cte.user_id
                WHERE c.position = ?
            )
            SELECT user_id FROM cte WHERE binary_sponsor = ? ORDER BY depth ASC LIMIT 1
        ";

        $result = \Illuminate\Support\Facades\DB::selectOne($query, [$sponsorId, $positionStr, $positionStr, $sponsorId]);

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
            'membershipActive' => true,
            'active' => $user->is_approved == 1,
            'qualified' => 1,
            'LeftPoints' => 0,  // TODO: Implement points query
            'RightPoints' => 0, // TODO: Implement points query
            'account_type' => ['id' => 1, 'account' => 'Socio']
        ];
    }
}
