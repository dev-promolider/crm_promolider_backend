<?php

namespace App\Services\MLM;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BinaryTreeService
{
    private const CACHE_KEY = 'mlm_global_binary_tree';

    /**
     * Extrae todos los usuarios de la base de datos y construye el árbol binario en memoria.
     * Luego guarda el resultado en Redis para acceso instantáneo.
     */
    public function buildTreeAndCache()
    {
        // 1. Obtener todos los usuarios
        $users = User::all()->keyBy('id')->toArray();
        
        // 2. Obtener las relaciones del árbol desde la tabla 'classified'
        $classifications = \Illuminate\Support\Facades\DB::table('classified')->get()->keyBy('user_id')->toArray();

        $nodes = [];
        $rootId = null; 
        
        // Fase 1: Instanciar todos los nodos puros en memoria (O(n))
        foreach ($users as $id => $userData) {
            $sponsorId = $userData['id_referrer_sponsor'] ?? null;
            if ($sponsorId && isset($users[$sponsorId])) {
                $sponsor = $users[$sponsorId];
                $userData['sponsor_name'] = trim(($sponsor['name'] ?? '') . ' ' . ($sponsor['last_name'] ?? ''));
            } else {
                $userData['sponsor_name'] = 'Ninguno';
            }
            $nodes[$id] = new BinaryNode($userData);
        }
        
        // Fase 1.5 ELIMINADA. La calificación se hará en la Fase 3 con DFS Post-Order para revisar toda la profundidad.
        
        // Fase 2: Enlazar los punteros padre-hijo (O(n)) usando 'classified'
        foreach ($users as $id => $userData) {
            
            // Buscar la clasificación del usuario actual
            $classification = $classifications[$id] ?? null;
            
            if ($classification && $classification->user_above && $classification->user_above !== 'top') {
                $parentId = (int) $classification->user_above;
                $position = (int) $classification->position; // 0 = Izquierda, 1 = Derecha
                
                if (isset($nodes[$parentId])) {
                    if ($position === 0) {
                        $nodes[$parentId]->left = $nodes[$id];
                    } elseif ($position === 1) {
                        $nodes[$parentId]->right = $nodes[$id];
                    }
                }
            } else {
                // Si user_above es 'top', entonces es la raíz principal
                if ($classification && $classification->user_above === 'top') {
                    $rootId = $id;
                } elseif (!$rootId && $id === 1) {
                    $rootId = $id; // Fallback por seguridad
                }
            }
        }
        
        // Fase 3: Calcular Calificación Automática Real (Profundidad infinita)
        if ($rootId && isset($nodes[$rootId])) {
            $this->calculateQualifications($nodes[$rootId], $users);
            
            $treeData = $nodes[$rootId]->toArray();
            
            // Guardar en caché serializado como JSON (Sin expiración)
            Cache::forever(self::CACHE_KEY, json_encode($treeData));
            
            return $treeData;
        }

        return null;
    }

    /**
     * Búsqueda Post-Order (DFS) para encontrar qué patrocinadores tienen directos activos
     * en el subárbol izquierdo y derecho, habilitando la calificación sin importar la profundidad.
     */
    private function calculateQualifications($node, &$users)
    {
        if (!$node) return [];

        $leftActiveSponsors = $this->calculateQualifications($node->left, $users);
        $rightActiveSponsors = $this->calculateQualifications($node->right, $users);

        // ¿El usuario actual está calificado? (Tiene al menos 1 directo activo a la izq y 1 a la der)
        $isQualified = isset($leftActiveSponsors[$node->userId]) && isset($rightActiveSponsors[$node->userId]);
        $node->rawUserData['qualified'] = $isQualified;

        // Combinar los conjuntos para retornar al padre
        $activeSponsors = $leftActiveSponsors + $rightActiveSponsors;

        // Si este nodo (usuario) es un socio activo, agregamos a SU patrocinador al conjunto
        $userData = $users[$node->userId] ?? null;
        if ($userData) {
            $now = now();
            $isRequestApproved = isset($userData['request']) && $userData['request'] == 2;
            $isActive = $isRequestApproved && (empty($userData['expiration_date']) || \Carbon\Carbon::parse($userData['expiration_date']) > $now);
            $isMembershipActive = $isRequestApproved && (!empty($userData['expiration_membership_date']) && \Carbon\Carbon::parse($userData['expiration_membership_date']) > $now);
            $idAccountType = $userData['id_account_type'] ?? null;

            if ($isActive && $isMembershipActive && $idAccountType != 5 && $idAccountType != 6) {
                $sponsorId = $userData['id_referrer_sponsor'] ?? null;
                if ($sponsorId) {
                    $activeSponsors[(int)$sponsorId] = true;
                }
            }
        }

        return $activeSponsors;
    }

    /**
     * Obtiene el árbol global, idealmente desde Redis para velocidad extrema.
     */
    public function getGlobalTree()
    {
        $cachedTree = Cache::get(self::CACHE_KEY);
        
        if ($cachedTree) {
            return json_decode($cachedTree, true);
        }
        
        // Si por alguna razón no está en caché, lo reconstruye.
        return $this->buildTreeAndCache();
    }
    
    /**
     * Obtiene el sub-árbol de un usuario en específico para enviarlo al Frontend.
     * Busca directamente en el JSON cacheado en RAM (hiper veloz).
     */
    public function getSubTreeForUser($userId)
    {
        $fullTree = $this->getGlobalTree();
        return $this->findNodeInTree($fullTree, $userId);
    }

    /**
     * Búsqueda recursiva (Depth-First Search) en memoria RAM.
     */
    private function findNodeInTree($nodeData, $userId)
    {
        if (!$nodeData) return null;
        if ($nodeData['id'] == $userId) return $nodeData;
        
        if (isset($nodeData['left']) && $nodeData['left'] !== null) {
            $leftResult = $this->findNodeInTree($nodeData['left'], $userId);
            if ($leftResult) return $leftResult;
        }
        
        if (isset($nodeData['right']) && $nodeData['right'] !== null) {
            $rightResult = $this->findNodeInTree($nodeData['right'], $userId);
            if ($rightResult) return $rightResult;
        }
        
        return null;
    }
}
