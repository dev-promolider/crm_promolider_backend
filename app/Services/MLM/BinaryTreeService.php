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
            $nodes[$id] = new BinaryNode($userData);
        }
        
        // Fase 1.5: Calcular el estado "Qualified" basándose en las dos patas inmediatas
        $sponsoredBy = []; // user_above => list of immediate children
        foreach ($classifications as $userId => $classification) {
            $userAbove = $classification->user_above;
            if ($userAbove && $userAbove !== 'top') {
                $sponsoredBy[(int)$userAbove][] = $classification;
            }
        }
        
        $now = now();
        foreach ($users as $id => $userData) {
            $leftActive = false;
            $rightActive = false;
            
            if (isset($sponsoredBy[$id])) {
                foreach ($sponsoredBy[$id] as $sponsoredClassification) {
                    $sponsoredId = (int) $sponsoredClassification->user_id;
                    $sponsoredUser = $users[$sponsoredId] ?? null;
                    
                    if ($sponsoredUser) {
                        $isRequestApproved = isset($sponsoredUser['request']) && $sponsoredUser['request'] == 2;
                        $isActive = $isRequestApproved && (empty($sponsoredUser['expiration_date']) || \Carbon\Carbon::parse($sponsoredUser['expiration_date']) > $now);
                        $isMembershipActive = $isRequestApproved && (!empty($sponsoredUser['expiration_membership_date']) && \Carbon\Carbon::parse($sponsoredUser['expiration_membership_date']) > $now);
                        $idAccountType = $sponsoredUser['id_account_type'] ?? null;
                        
                        if ($isActive && $isMembershipActive && $idAccountType != 5 && $idAccountType != 6) {
                            if ($sponsoredClassification->position == 0) $leftActive = true;
                            if ($sponsoredClassification->position == 1) $rightActive = true;
                        }
                    }
                    if ($leftActive && $rightActive) break;
                }
            }
            
            // Inyectamos la variable calificado en el rawUserData para que BinaryNode la lea
            $nodes[$id]->rawUserData['qualified'] = ($leftActive && $rightActive);
        }
        
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
        
        if ($rootId && isset($nodes[$rootId])) {
            $treeData = $nodes[$rootId]->toArray();
            
            // Guardar en caché serializado como JSON (Sin expiración)
            Cache::forever(self::CACHE_KEY, json_encode($treeData));
            
            return $treeData;
        }

        return null;
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
