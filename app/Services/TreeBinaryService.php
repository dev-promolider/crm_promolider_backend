<?php

namespace App\Services;

use App\Models\User;
use App\Models\Classified;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class TreeBinaryService
{
    /**
     * Construye y lista un árbol binario de 2 niveles para el usuario autenticado.
     * Utiliza una CTE recursiva en SQL para evitar OOM al escalar a miles de usuarios.
     * 100% Optimizado.
     */
    public function listbinary(): AnonymousResourceCollection
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return JsonResource::collection([]);
        }

        $data = ['c' => $currentUser];

        $nodeA = $this->findDirectSponsoredChildDb($currentUser, 0);
        $nodeB = $this->findDirectSponsoredChildDb($currentUser, 1);

        if ($nodeA) {
            $data['a'] = $nodeA;
            $nodeAa = $this->findDirectSponsoredChildDb($nodeA->user, 0);
            if ($nodeAa) $data['aa'] = $nodeAa;
            
            $nodeAb = $this->findDirectSponsoredChildDb($nodeA->user, 1);
            if ($nodeAb) $data['ab'] = $nodeAb;
        }

        if ($nodeB) {
            $data['b'] = $nodeB;
            $nodeBa = $this->findDirectSponsoredChildDb($nodeB->user, 0);
            if ($nodeBa) $data['ba'] = $nodeBa;
            
            $nodeBb = $this->findDirectSponsoredChildDb($nodeB->user, 1);
            if ($nodeBb) $data['bb'] = $nodeBb;
        }

        foreach ($data as $key => $node) {
            $userToAppend = $key === 'c' ? $node : ($node->user ?? null);
            if ($userToAppend) {
                $userToAppend->append(['LeftPoints', 'RightPoints', 'qualified']);
            }
        }

        return JsonResource::collection(collect($data));
    }

    private function findDirectSponsoredChildDb(User $sponsorUser, int $position): ?Classified
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
            SELECT id FROM cte WHERE id_user_sponsor = ? ORDER BY depth ASC LIMIT 1
        ";

        $result = DB::selectOne($query, [
            $sponsorUser->id, 
            $position, 
            $position, 
            $sponsorUser->id
        ]);

        if ($result) {
            return Classified::with('user.accountType')->find($result->id);
        }

        return null;
    }
}
