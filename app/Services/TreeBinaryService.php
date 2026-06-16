<?php

namespace App\Services;

use App\Models\User;
use App\Models\Classified;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TreeBinaryService
{
    /**
     * Construye y lista un árbol binario de 2 niveles para el usuario autenticado.
     * La búsqueda de hijos se basa en encontrar el primer descendiente en cada pierna
     * que fue patrocinado directamente por el nodo padre.
     */
    public function listbinary(): AnonymousResourceCollection
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return JsonResource::collection([]);
        }

        // Cargar todos los clasificados en memoria para evitar N+1 o loops de queries lentas
        $allClassifieds = Classified::with('user.accountType')->get();
        // Indexamos por user_above para búsqueda rápida en memoria
        $classifiedsByAbove = $allClassifieds->groupBy('user_above');

        $data = ['c' => $currentUser];

        $nodeA = $this->findDirectSponsoredChildMem($currentUser, 0, $classifiedsByAbove);
        $nodeB = $this->findDirectSponsoredChildMem($currentUser, 1, $classifiedsByAbove);

        if ($nodeA) {
            $data['a'] = $nodeA;
            $nodeAa = $this->findDirectSponsoredChildMem($nodeA->user, 0, $classifiedsByAbove);
            if ($nodeAa) $data['aa'] = $nodeAa;
            
            $nodeAb = $this->findDirectSponsoredChildMem($nodeA->user, 1, $classifiedsByAbove);
            if ($nodeAb) $data['ab'] = $nodeAb;
        }

        if ($nodeB) {
            $data['b'] = $nodeB;
            $nodeBa = $this->findDirectSponsoredChildMem($nodeB->user, 0, $classifiedsByAbove);
            if ($nodeBa) $data['ba'] = $nodeBa;
            
            $nodeBb = $this->findDirectSponsoredChildMem($nodeB->user, 1, $classifiedsByAbove);
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

    private function findDirectSponsoredChildMem(User $sponsorUser, int $position, $classifiedsByAbove): ?Classified
    {
        $currentUserAboveId = $sponsorUser->id;
        $maxDepth = 100;

        for ($i = 0; $i < $maxDepth; $i++) {
            $children = $classifiedsByAbove->get($currentUserAboveId);
            if (!$children) {
                return null;
            }

            $nextNodeInLeg = $children->firstWhere('position', $position);

            if (!$nextNodeInLeg) {
                return null;
            }

            if ($nextNodeInLeg->id_user_sponsor == $sponsorUser->id) {
                return $nextNodeInLeg;
            }

            $currentUserAboveId = $nextNodeInLeg->user_id;

            if (!$currentUserAboveId) {
                return null;
            }
        }
        
        return null;
    }
}
