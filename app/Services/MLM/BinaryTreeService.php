<?php

namespace App\Services\MLM;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinaryTreeService
{
    private const CACHE_KEY = 'mlm_global_binary_tree';

    /**
     * Invalida el arbol cacheado y encola su reconstruccion.
     *
     * Primero se borra la clave y despues se encola: si la cola no tiene worker, la
     * siguiente lectura reconstruye el arbol sobre la marcha en vez de seguir sirviendo
     * uno viejo. Antes la caché era Cache::forever y solo se reconstruia al crear un
     * usuario, es decir, antes de que existiera su fila en 'classified': el recien
     * registrado nunca aparecia en su propio arbol.
     */
    public static function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        \App\Jobs\RebuildBinaryTreeCache::dispatch();
    }

    /**
     * Extrae todos los usuarios de la base de datos y construye el árbol binario en memoria.
     * Luego guarda el resultado en Redis para acceso instantáneo.
     */
    public function buildTreeAndCache()
    {
        // 1. Obtener todos los usuarios
        $users = User::all()->keyBy('id')->toArray();
        
        // 2. Obtener las relaciones del árbol desde la tabla 'classified'
        $classifications = DB::table('classified')->get()->keyBy('user_id')->toArray();

        // 3. Obtener puntos activos agrupados por usuario y lado
        $activePointsRaw = DB::table('points')
            ->select('sponsor_id', 'side', DB::raw('SUM(points) as total'))
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
        
        // Fase 2: Enlazar los punteros padre-hijo (O(n)) usando 'classified'
        foreach ($users as $id => $userData) {

            // Buscar la clasificación del usuario actual
            $classification = $classifications[$id] ?? null;

            if ($classification && $classification->user_above && $classification->user_above !== 'top') {
                $parentId = (int) $classification->user_above;
                $position = (int) $classification->position; // 0 = Izquierda, 1 = Derecha

                if (isset($nodes[$parentId])) {
                    // Un nodo binario admite un hijo por pierna. Bajo el nodo raiz hay
                    // seis en la misma, herencia del monolito: solo uno se dibuja y los
                    // demas desaparecen con todo su subarbol. No se cambia cual gana
                    // —eso movería ramas enteras de sitio— pero al menos deja de pasar
                    // en silencio: hay que repararlo en los datos.
                    $ocupado = $position === 0 ? $nodes[$parentId]->left : $nodes[$parentId]->right;

                    if ($ocupado !== null) {
                        Log::warning('[ARBOL BINARIO] Dos usuarios en la misma pierna del mismo padre', [
                            'padre'       => $parentId,
                            'posicion'    => $position,
                            'desplazado'  => $ocupado->userId,
                            'se_queda'    => $id,
                        ]);
                    }

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
        
        // Fase 3: Calificación, con la misma regla que usan el panel y el corte.
        if ($rootId && isset($nodes[$rootId])) {
            $calificados = app(QualificationService::class)->qualifiedMap();

            foreach ($nodes as $id => $node) {
                $node->rawUserData['qualified'] = $calificados[$id] ?? false;
            }

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

    /**
     * Determina la pierna más débil (menor volumen de puntos o cantidad de miembros) de un patrocinador.
     * Retorna: 0 para Izquierda, 1 para Derecha.
     */
    public function getWeakerLeg(int $sponsorId): int
    {
        // 1. Comparar los puntos acumulados activos en la tabla points
        $points = DB::table('points')
            ->select('side', DB::raw('SUM(points) as total'))
            ->where('sponsor_id', $sponsorId)
            ->where('status', 1)
            ->groupBy('side')
            ->pluck('total', 'side')
            ->toArray();

        $leftPoints = (float) ($points[0] ?? 0);
        $rightPoints = (float) ($points[1] ?? 0);

        if ($leftPoints < $rightPoints) {
            return 0; // Izquierda es más débil
        } elseif ($rightPoints < $leftPoints) {
            return 1; // Derecha es más débil
        }

        // 2. Fallback: Si los puntos son iguales, contamos los miembros en cada subárbol
        $leftCount = $this->countBranchMembers($sponsorId, 0);
        $rightCount = $this->countBranchMembers($sponsorId, 1);

        if ($leftCount <= $rightCount) {
            return 0; // Izquierda por defecto o si es menor
        }
        return 1;
    }

    private function countBranchMembers(int $sponsorId, int $position): int
    {
        // Obtener el hijo directo en esa posición
        $directChild = DB::table('classified')
            ->where('user_above', $sponsorId)
            ->where('position', $position)
            ->value('user_id');

        if (!$directChild) {
            return 0;
        }

        // Carga rápida del mapa de relaciones en memoria
        $relations = DB::table('classified')
            ->select('user_id', 'user_above')
            ->whereNotNull('user_above')
            ->get()
            ->groupBy('user_above');

        return $this->traverseAndCount($directChild, $relations);
    }

    private function traverseAndCount(int $currentId, $relations): int
    {
        $count = 1;
        if (isset($relations[$currentId])) {
            foreach ($relations[$currentId] as $child) {
                $count += $this->traverseAndCount((int)$child->user_id, $relations);
            }
        }
        return $count;
    }
}
