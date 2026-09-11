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

    public function getWidgetsData(int $userId, string $timeframe = 'normal'): array
    {
        $user = User::find($userId);

        // Las tres condiciones salen de los accesores del modelo, que son los mismos
        // que usan el corte y el árbol. Antes el panel las calculaba por su cuenta:
        // miraba los hijos inmediatos del árbol en vez de los patrocinados directos,
        // así que podía mostrar "Calificado" en verde mientras el corte pagaba cero.
        $isMembershipActive = $user->membershipActive;
        $isActive = $user->active;
        $isQualified = $user->qualified;

        $wallet = \Illuminate\Support\Facades\DB::table('wallet')->where('user_id', $userId)->first();
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

        // El bono de inicio rápido es este, no el de expansión. El panel enseñaba el de
        // expansión bajo la etiqueta «Inicio Rápido», y como no se generaba nunca,
        // marcaba siempre $0.00.
        $fastCashMonthly = $monthlyQuery(function ($query) {
            $query->where('reason', 'LIKE', '%Bono de efectivo rápido%')
                  ->orWhere('reason', 'LIKE', '%Bono de efectivo rapido%');
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
                'fast_cash' => round((float)$fastCashMonthly, 2),
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
                'id_referrer_sponsor', 'id_account_type', 'expiration_membership_date', 'request', 'expiration_date'
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

        $classifications = \Illuminate\Support\Facades\DB::table('classified')->get()->keyBy('user_id')->toArray();

        $buildTree = function($currentUser, $depth = 1) use (&$buildTree, &$childrenMap, $classifications, $userId) {
            $children = $childrenMap[$currentUser->id] ?? [];
            $formattedDirects = [];
            
            foreach ($children as $child) {
                $leg = 'none';
                $currentId = $child->id;
                
                while (isset($classifications[$currentId]) && $classifications[$currentId]->user_above !== 'top') {
                    $parentId = (int) $classifications[$currentId]->user_above;
                    $position = (int) $classifications[$currentId]->position;
                    
                    if ($parentId === $userId) {
                        $leg = ($position === 0) ? 'Izquierda' : 'Derecha';
                        break;
                    }
                    $currentId = $parentId;
                    
                    if ($currentId === $child->id) break;
                }

                $membershipActive = (is_null($child->expiration_membership_date) || $child->expiration_membership_date > now()) ? 1 : 0;

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
                    'active' => (is_null($child->expiration_date) || $child->expiration_date > now()) && ($child->request == 2) ? 1 : 0,
                    'membershipActive' => $membershipActive,
                    'leg' => $leg,
                    'generation' => $depth,
                    'account_type' => ['id' => $child->id_account_type, 'account' => 'Socio']
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
                'active' => (is_null($rootUser->expiration_date) || $rootUser->expiration_date > now()) && ($rootUser->request == 2),
                'membershipActive' => (is_null($rootUser->expiration_membership_date) || $rootUser->expiration_membership_date > now()) && ($rootUser->request == 2),
                'account_type' => ['id' => $rootUser->id_account_type, 'account' => 'Socio']
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
        $query = "
            WITH RECURSIVE cte AS (
                SELECT id, user_id, user_above, id_user_sponsor, position, 1 as depth
                FROM classified
                WHERE user_above = CAST(? AS CHAR) AND position = ?
                
                UNION ALL
                
                SELECT c.id, c.user_id, c.user_above, c.id_user_sponsor, c.position, cte.depth + 1
                FROM classified c
                INNER JOIN cte ON c.user_above = CAST(cte.user_id AS CHAR)
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
