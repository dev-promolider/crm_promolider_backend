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
            'rank' => $this->rangoParaBarra($userId, $rank),
            'membership' => $this->membresiaParaBarra($user),
            'points' => [
                'total' => $points,
                'percentage' => $percentage
            ],
            'notifications' => [
                'unread' => $unreadNotifications
            ]
        ];
    }

    /**
     * El rango para la barra superior: el actual con sus requisitos, el anterior y si
     * acaba de subir.
     *
     * El ingeniero pidió que el cambio de rango se note: la insignia es lo que el
     * afiliado enseña a otros, y a 20 px no se distinguía un rango de otro.
     */
    private function rangoParaBarra(int $userId, $rank): array
    {
        $asignaciones = \Illuminate\Support\Facades\DB::table('rank_binary')
            ->join('rank_bonus', 'rank_bonus.id', '=', 'rank_binary.rank_id')
            ->where('rank_binary.user_id', $userId)
            ->orderByDesc('rank_binary.batch')
            ->orderByDesc('rank_binary.id')
            ->limit(2)
            ->get(['rank_bonus.id', 'rank_bonus.name', 'rank_bonus.icon', 'rank_bonus.sort_order', 'rank_binary.created_at']);

        $actual = $asignaciones->get(0);
        $anterior = $asignaciones->get(1);

        return [
            'name'         => $rank->name ?? 'Sin rango',
            'icon'         => $rank->icon ?? null,
            'level'        => $rank->id ?? 0,
            'requirements' => $rank ? [
                'vol_min'          => (float) $rank->vol_min,
                'active_direct'    => (int) $rank->active_direct,
                'pack_max'         => (int) $rank->pack_max,
                'max_pay'          => (float) $rank->max_pay,
                'limit_generation' => (int) $rank->limit_generation,
                'monthly_bonus'    => (float) ($rank->monthly_bonus ?? 0),
            ] : null,
            'achieved_at'  => $actual->created_at ?? null,
            'promoted'     => $actual && $anterior && (int) $actual->sort_order > (int) $anterior->sort_order,
            'previous'     => $anterior ? ['name' => $anterior->name, 'icon' => $anterior->icon] : null,
        ];
    }

    /**
     * La membresía del afiliado y su distintivo, si la membresía lo tiene configurado
     * (por ejemplo, FOUNDERS LEGACY o Socio Fundador).
     */
    private function membresiaParaBarra($user): ?array
    {
        if (!$user || !$user->id_account_type) {
            return null;
        }

        $membresia = \App\Models\AccountType::find($user->id_account_type);

        if (!$membresia) {
            return null;
        }

        return [
            'name'         => $membresia->account,
            'highlight'    => app(\App\Services\MLM\MembershipRules::class)->distintivo((int) $membresia->id),
            'is_permanent' => (bool) $membresia->is_permanent,
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

        // Los importes se agrupan por tipo de bono y no por el texto del motivo. Filtrar
        // por texto fallaba en silencio: «Regalías de Autor» buscaba «Bono de productor»
        // y los movimientos reales dicen «Bono por compra de curso de…», así que salía
        // siempre en cero, y «Ventas de Afiliado» sumaba juntos creador y venta. Con los
        // nombres nuevos del plan, filtrar por texto se habría roto del todo.
        $sumaPorTipo = function (?\Carbon\Carbon $desde) use ($walletId) {
            $query = \Illuminate\Support\Facades\DB::table('wallet_movements')
                ->where('wallet_id', $walletId)
                ->whereNotNull('bonus_type_id')
                ->where('amount', '>', 0);

            if ($desde) {
                $query->where('created_at', '>=', $desde);
            }

            return $query->select('bonus_type_id', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total'))
                ->groupBy('bonus_type_id')
                ->pluck('total', 'bonus_type_id');
        };

        $importe = fn ($sumas, int $tipo) => round((float) ($sumas->get($tipo) ?? 0), 2);

        // Vista normal: este mes. Vista histórica: desde el alta.
        $delPeriodo = $sumaPorTipo($timeframe === 'historical' ? null : now()->startOfMonth());

        // Obtener la fecha del último corte binario para este usuario
        $lastCutDate = null;
        if ($timeframe !== 'historical') {
            $lastCutDate = \Illuminate\Support\Facades\DB::table('binary_cut_histories')
                ->where('user_id', $userId)
                ->max('created_at');
        }

        $acumulado = $sumaPorTipo($lastCutDate ? \Carbon\Carbon::parse($lastCutDate) : null);

        // 1 efectivo rápido · 2 venta de cursos · 3 creador · 4 binario · 5 generacional
        // 6 expansión · 7 estabilidad de rango
        $porTipo = fn ($sumas) => [
            'fast_cash'      => $importe($sumas, 1),
            'course_sale'    => $importe($sumas, 2),
            'producer'       => $importe($sumas, 3),
            'binary'         => $importe($sumas, 4),
            'generational'   => $importe($sumas, 5),
            'expansion'      => $importe($sumas, 6),
            'rank_stability' => $importe($sumas, 7),
        ];

        $mensual = $porTipo($delPeriodo);
        $acumulados = $porTipo($acumulado);

        return [
            'conditions' => [
                'membershipActive' => $isMembershipActive,
                'active' => $isActive,
                'qualified' => $isQualified
            ],
            'last_cut_date' => $lastCutDate ? \Carbon\Carbon::parse($lastCutDate)->format('d/m/Y, H:i') : null,
            'monthly_bonuses' => $mensual,
            'cumulative_bonuses' => $acumulados,
            'total' => round(array_sum($timeframe === 'historical' ? $acumulados : $mensual), 2),
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
