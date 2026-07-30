<?php

namespace App\Services\MLM;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        
        // 2. Obtener las relaciones del árbol desde la tabla 'binary_tree'
        $classifications = DB::table('binary_tree')->get()->keyBy('user_id')->toArray();

        // 3. Obtener puntos activos agrupados por usuario y lado
        $activePointsRaw = DB::table('points')
            ->select('sponsor_id', 'side', DB::raw('SUM(points_val) as total'))
            ->where('status', 1)
            ->groupBy('sponsor_id', 'side')
            ->get();
            
        $activePoints = [];
        foreach ($activePointsRaw as $row) {
            if (!isset($activePoints[$row->sponsor_id])) {
                $activePoints[$row->sponsor_id] = ['left' => 0, 'right' => 0];
            }
            if ($row->side == 0) {
                $activePoints[$row->sponsor_id]['left'] = $row->total;
            } else {
                $activePoints[$row->sponsor_id]['right'] = $row->total;
            }
        }

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
            
            // Inyectar puntos sumados
            $userData['left_points'] = $activePoints[$id]['left'] ?? 0;
            $userData['right_points'] = $activePoints[$id]['right'] ?? 0;
            
            $nodes[$id] = new BinaryNode($userData);
        }
        
        // Fase 1.5 ELIMINADA. La calificación se hará en la Fase 3 con DFS Post-Order para revisar toda la profundidad.
        
        // Fase 2: Enlazar los punteros padre-hijo (O(n)) usando 'classified'
        foreach ($users as $id => $userData) {
            
            // Buscar la clasificación del usuario actual
            $classification = $classifications[$id] ?? null;
            
            if ($classification && $classification->user_above) {
                $parentId = (int) $classification->user_above;
                $position = $classification->position; // 'L' = Izquierda, 'R' = Derecha
                
                if (isset($nodes[$parentId])) {
                    if ($position === 'L') {
                        $nodes[$parentId]->left = $nodes[$id];
                    } elseif ($position === 'R') {
                        $nodes[$parentId]->right = $nodes[$id];
                    }
                }
            } else {
                // Si no hay user_above, entonces es la raíz principal
                if ($classification && !$classification->user_above) {
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
            $isActive = $userData['is_approved'] == 1;
            $isMembershipActive = true; // Mock temporal hasta integrar account_type_details

            if ($isActive && $isMembershipActive) {
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
