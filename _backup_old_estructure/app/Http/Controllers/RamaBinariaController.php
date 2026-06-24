<?php

namespace App\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\User;
use App\Models\Classified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http; // <-- Importante: Añadimos el Facade HTTP
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TreeBinaryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RamaBinariaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    const DEFAULT_EMPTY_NODE = [
        "id"     => 0,
        "name"   => "Disponible",
        "photo"    => "https://cdn.icon-icons.com/icons2/1465/PNG/512/756exclamationmark_100528.png",
        "children" => [],
    ];

    public function listbinary(TreeBinaryService $treebinary): AnonymousResourceCollection
    {
        return $treebinary->listbinary();
    }

    /**
     * Método equivalente a la API externa (/binaryTree/:id) 
     * pero usando datos locales de la base de datos
     * 
     * DIFERENCIAS CLAVE con getBinaryThreeDataInternal():
     * - Usa 'user_above' en lugar de 'id_user_sponsor' 
     * - Carga todos los datos en memoria primero (como la API externa)
     * - Usa un enfoque de mapas para mejor rendimiento
     */
    public function getBinaryTreeFromLocalData($userId = null): JsonResponse
    {
        try {
            // Usar el ID del usuario autenticado si no se proporciona
            $rootUserId = $userId ?: auth()->user()->id;

            Log::info("=== INICIO getBinaryTreeFromLocalData (Equivalente a API Externa) ===", [
                'root_user_id' => $rootUserId
            ]);

            // 1. OBTENER TODOS LOS USUARIOS (equivalente a la primera query de la API externa)
            $allUsers = DB::table('users as u')
                ->leftJoin('classified as c', 'u.id', '=', 'c.user_id')
                ->select([
                    'u.id',
                    'u.name', 
                    'u.last_name',
                    'u.photo',
                    'u.expiration_membership_date',
                    'u.id_account_type',
                    'c.id_user_sponsor'
                ])
                ->get();

            Log::info("Usuarios cargados", ['total_users' => $allUsers->count()]);

            // 2. OBTENER DATOS DE CLASSIFIED (equivalente a la segunda query de la API externa)
            $allClassifiedData = DB::table('classified')
                ->select(['user_id', 'position', 'user_above'])
                ->whereNotNull('user_above')
                ->get();

            Log::info("Datos classified cargados", ['total_classified' => $allClassifiedData->count()]);

            // 3. CREAR MAPA DE USUARIOS (equivalente al usersMap de la API externa)
            $usersMap = [];
            foreach ($allUsers as $user) {
                $usersMap[$user->id] = $user;
            }

            // 4. CREAR MAPA DE HIJOS (equivalente al childrenMap de la API externa)
            $childrenMap = [];
            foreach ($allClassifiedData as $row) {
                $parentId = (int) $row->user_above;
                if (!$parentId) continue;

                // Inicializar si no existe
                if (!isset($childrenMap[$parentId])) {
                    $childrenMap[$parentId] = ['left' => [], 'right' => []];
                }

                // Agregar hijo según posición
                if ($row->position == 0) {
                    $childrenMap[$parentId]['left'][] = $row->user_id;
                } elseif ($row->position == 1) {
                    $childrenMap[$parentId]['right'][] = $row->user_id;
                }
            }

            Log::info("Mapas construidos", [
                'users_in_map' => count($usersMap),
                'parents_with_children' => count($childrenMap)
            ]);

            // 5. VERIFICAR QUE EL USUARIO ROOT EXISTE
            if (!isset($usersMap[$rootUserId])) {
                Log::error("Usuario root no encontrado", ['root_user_id' => $rootUserId]);
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }

            // 6. CONSTRUIR ÁRBOL RECURSIVAMENTE
            $binaryTree = $this->buildTreeRecursiveFromMaps($rootUserId, $usersMap, $childrenMap);

            Log::info("Árbol binario construido exitosamente desde datos locales", [
                'root_id' => $binaryTree['id'],
                'root_name' => $binaryTree['name'],
                'has_left' => isset($binaryTree['left']),
                'has_right' => isset($binaryTree['right'])
            ]);

            return response()->json($binaryTree);

        } catch (\Throwable $th) {
            Log::error("Error en getBinaryTreeFromLocalData", [
                'root_user_id' => $rootUserId ?? 'N/A',
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);

            return response()->json([
                'message' => 'Error procesando la solicitud del árbol binario',
                'error' => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    /**
     * Construye el árbol recursivamente usando los mapas precargados
     * (Equivalente a la función buildTreeRecursive de la API externa)
     */
    private function buildTreeRecursiveFromMaps($userId, $usersMap, $childrenMap, $level = 0): ?array
    {
        // Límite de recursión para evitar bucles infinitos
        if ($level > 15) {
            Log::warning("Límite de recursión alcanzado", ['user_id' => $userId, 'level' => $level]);
            return null;
        }

        // Verificar que el usuario existe en el mapa
        if (!isset($usersMap[$userId])) {
            Log::warning("Usuario no encontrado en mapa", ['user_id' => $userId]);
            return null;
        }

        $user = $usersMap[$userId];

        Log::info("Construyendo nodo desde mapas", [
            'user_id' => $userId,
            'name' => $user->name,
            'level' => $level
        ]);

        // Estructura base del nodo (formato compatible con Vue)
        $node = [
            'id' => $user->id,
            'name' => trim($user->name . ' ' . ($user->last_name ?? '')),
            'photo' => ParseUrl::contacAtrrS3($user->photo ?? ''),
            'expiration_membership_date' => $user->expiration_membership_date,
            'id_account_type' => $user->id_account_type,
            'id_user_sponsor' => $user->id_user_sponsor
        ];

        // Buscar hijos en el mapa
        if (isset($childrenMap[$userId])) {
            $children = $childrenMap[$userId];

            // Procesar hijo izquierdo (position = 0)
            if (!empty($children['left'])) {
                $leftChildId = $children['left'][0]; // Tomar el primero si hay múltiples
                Log::info("Procesando hijo izquierdo", [
                    'parent_id' => $userId,
                    'left_child_id' => $leftChildId
                ]);

                $leftSubtree = $this->buildTreeRecursiveFromMaps($leftChildId, $usersMap, $childrenMap, $level + 1);
                if ($leftSubtree) {
                    $node['left'] = $leftSubtree;
                }
            }

            // Procesar hijo derecho (position = 1)
            if (!empty($children['right'])) {
                $rightChildId = $children['right'][0]; // Tomar el primero si hay múltiples
                Log::info("Procesando hijo derecho", [
                    'parent_id' => $userId,
                    'right_child_id' => $rightChildId
                ]);

                $rightSubtree = $this->buildTreeRecursiveFromMaps($rightChildId, $usersMap, $childrenMap, $level + 1);
                if ($rightSubtree) {
                    $node['right'] = $rightSubtree;
                }
            }
        }

        Log::info("Nodo construido desde mapas", [
            'node_id' => $node['id'],
            'has_left' => isset($node['left']),
            'has_right' => isset($node['right']),
            'level' => $level
        ]);

        return $node;
    }

    /**
     * Endpoint público que puede recibir un ID específico
     * (Equivalente directo al endpoint /binaryTree/:id de la API externa)
     */
    public function getBinaryTreeForUser($id = null): JsonResponse
    {
        $userId = $id ? (int) $id : auth()->user()->id;

        if (!$userId || $userId <= 0) {
            return response()->json([
                'message' => 'El ID de usuario proporcionado no es un número válido.'
            ], 400);
        }

        return $this->getBinaryTreeFromLocalData($userId);
    }

    /**
     * Método para debuggear la estructura del árbol binario
     * Ayuda a identificar por qué no se muestran todos los usuarios
     */
    public function debugBinaryTreeStructure(): JsonResponse
    {
        try {
            $rootUserId = auth()->user()->id;
            
            Log::info("=== DEBUG: Analizando estructura del árbol binario ===");
        
            // 1. OBTENER TODOS LOS DATOS CLASIFICADOS CON DETALLES
            $allClassified = DB::table('classified as c')
                ->join('users as u', 'c.user_id', '=', 'u.id')
                ->select([
                    'c.user_id',
                    'c.position', 
                    'c.user_above',
                    'c.id_user_sponsor',
                    'u.name',
                    'u.username'
                ])
                ->orderBy('c.user_id')
                ->get();
                
            Log::info("=== TODOS LOS REGISTROS CLASSIFIED ===", [
                'total' => $allClassified->count()
            ]);
        
            $debugInfo = [
                'total_users' => $allClassified->count(),
                'users_with_user_above' => 0,
                'users_without_user_above' => 0,
                'orphaned_users' => [],
                'connected_users' => [],
                'hierarchy_summary' => []
            ];
        
            foreach ($allClassified as $user) {
                $userInfo = [
                    'id' => $user->user_id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'position' => $user->position,
                    'user_above' => $user->user_above,
                    'id_user_sponsor' => $user->id_user_sponsor
                ];
            
                if ($user->user_above) {
                    $debugInfo['users_with_user_above']++;
                    $debugInfo['connected_users'][] = $userInfo;
                } else {
                    $debugInfo['users_without_user_above']++;
                    $debugInfo['orphaned_users'][] = $userInfo;
                }
            
                // Log individual para análisis
                Log::info("Usuario analizado", $userInfo);
            }
        
            // 2. ANALIZAR JERARQUÍAS POR NIVELES
            $hierarchyLevels = [];
            foreach ($allClassified as $user) {
                if ($user->user_above) {
                    $parentId = $user->user_above;
                    if (!isset($hierarchyLevels[$parentId])) {
                        $hierarchyLevels[$parentId] = ['left' => [], 'right' => []];
                    }
                    
                    if ($user->position == 0) {
                        $hierarchyLevels[$parentId]['left'][] = [
                            'id' => $user->user_id,
                            'name' => $user->name
                        ];
                    } elseif ($user->position == 1) {
                        $hierarchyLevels[$parentId]['right'][] = [
                            'id' => $user->user_id,
                            'name' => $user->name
                        ];
                    }
                }
            }
        
            $debugInfo['hierarchy_summary'] = $hierarchyLevels;
        
            // 3. VERIFICAR CONECTIVIDAD DESDE EL ROOT
            $connectedToRoot = $this->findAllConnectedUsers($rootUserId, $hierarchyLevels);
            $debugInfo['connected_to_root'] = count($connectedToRoot);
            $debugInfo['connected_to_root_ids'] = $connectedToRoot;
        
            // 4. IDENTIFICAR USUARIOS DESCONECTADOS
            $allUserIds = $allClassified->pluck('user_id')->toArray();
            $disconnected = array_diff($allUserIds, $connectedToRoot);
            $debugInfo['disconnected_users'] = $disconnected;
            $debugInfo['disconnected_count'] = count($disconnected);
        
            Log::info("=== RESUMEN DEBUG ===", $debugInfo);
        
            return response()->json([
                'debug_info' => $debugInfo,
                'recommendations' => $this->getRecommendations($debugInfo)
            ]);
        
        } catch (\Throwable $th) {
            Log::error("Error en debug", ['error' => $th->getMessage()]);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    
    /**
     * Encuentra todos los usuarios conectados recursivamente desde un nodo raíz
     */
    private function findAllConnectedUsers($rootId, $hierarchyLevels, &$visited = []): array
    {
        if (in_array($rootId, $visited)) {
            return $visited; // Evitar bucles infinitos
        }
    
        $visited[] = $rootId;
    
        if (isset($hierarchyLevels[$rootId])) {
            // Procesar hijos izquierdos
            foreach ($hierarchyLevels[$rootId]['left'] as $leftChild) {
                $this->findAllConnectedUsers($leftChild['id'], $hierarchyLevels, $visited);
            }
            
            // Procesar hijos derechos
            foreach ($hierarchyLevels[$rootId]['right'] as $rightChild) {
                $this->findAllConnectedUsers($rightChild['id'], $hierarchyLevels, $visited);
            }
        }
    
        return $visited;
    }
    
    /**
     * Genera recomendaciones basadas en el análisis
     */
    private function getRecommendations($debugInfo): array
    {
        $recommendations = [];
    
        if ($debugInfo['users_without_user_above'] > 1) {
            $recommendations[] = "Tienes {$debugInfo['users_without_user_above']} usuarios sin 'user_above'. Solo debería haber 1 (el root/admin).";
        }
    
        if ($debugInfo['disconnected_count'] > 0) {
            $recommendations[] = "Hay {$debugInfo['disconnected_count']} usuarios desconectados del árbol principal. Revisa los campos 'user_above' y 'position'.";
        }
    
        if (empty($recommendations)) {
            $recommendations[] = "La estructura parece correcta. Todos los usuarios están conectados al árbol.";
        }
    
        return $recommendations;
    }
    
    /**
     * Método mejorado que incluye TODOS los usuarios,
     * incluso los que no están en la jerarquía binaria
     */
    public function getBinaryTreeWithAllUsers($userId = null): JsonResponse
    {
        try {
            $rootUserId = $userId ?: auth()->user()->id;
            
            Log::info("=== CONSTRUYENDO ÁRBOL CON TODOS LOS USUARIOS ===", [
                'root_user_id' => $rootUserId
            ]);
        
            // Usar el método original para obtener el árbol principal
            $mainTreeResponse = $this->getBinaryTreeFromLocalData($rootUserId);
            $mainTree = json_decode($mainTreeResponse->getContent(), true);
        
            // Obtener usuarios desconectados
            $debugResponse = $this->debugBinaryTreeStructure();
            $debugData = json_decode($debugResponse->getContent(), true);
            
            $disconnectedUsers = $debugData['debug_info']['orphaned_users'];
        
            // Agregar usuarios desconectados como nodos adicionales
            $result = [
                'main_tree' => $mainTree,
                'disconnected_users' => $disconnectedUsers,
                'stats' => [
                    'connected_users' => $debugData['debug_info']['connected_to_root'],
                    'disconnected_users' => $debugData['debug_info']['disconnected_count'],
                    'total_users' => $debugData['debug_info']['total_users']
                ]
            ];
        
            return response()->json($result);
        
        } catch (\Throwable $th) {
            Log::error("Error en getBinaryTreeWithAllUsers", ['error' => $th->getMessage()]);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtiene el árbol binario desde el endpoint externo.
     *
     * Esta función llama a la API de Node.js para construir y devolver
     * la estructura completa del árbol binario para el usuario autenticado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBinaryThreeDataOrden(): JsonResponse
    {
        try {
            // 1. Obtenemos el ID del usuario autenticado.
            $userId = auth()->user()->id;

            // 2. Definimos la URL base del servicio externo.
            $baseUrl = 'https://bot.jungleclick.online';

            // 3. Construimos la URL completa del endpoint con el ID del usuario.
            $url = "{$baseUrl}/binaryTree/{$userId}";

            // 4. Realizamos la petición GET al servicio externo.
            // Añadimos un timeout para evitar que la petición se quede colgada indefinidamente.
            $response = Http::get($url);

            // 5. Verificamos si la respuesta de la API fue exitosa.
            if ($response->successful()) {
                // Si fue exitosa, devolvemos el contenido JSON de la respuesta.
                return response()->json($response->json());
            }

            // Si la respuesta no fue exitosa (error 4xx o 5xx), registramos el error y devolvemos una respuesta de error.
            Log::error("Error al llamar a la API del árbol binario para el usuario {$userId}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'message' => 'No se pudo obtener la estructura del árbol binario en este momento.',
                'error' => $response->body() // Opcional: devolver el cuerpo del error para depuración
            ], $response->status());

        } catch (\Throwable $th) {
            // Capturamos cualquier otra excepción (ej. timeout, error de conexión)
            Log::error("Excepción al llamar a la API del árbol binario: " . $th->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar la solicitud del árbol binario.'
            ], 500);
        }
    }


    public function getBinaryThreeData()
    {
        $user = User::select('id', 'name', 'photo')->where('id', auth()->user()->id)->get()->first();
        $childrens = $this->getDirects($user->id);
        $data = array('id' => $user->id, 'name' => $user->name, 'photo' => $user->photo, 'children' => $childrens);
        return $data;
    }


    public function getDirects($parent_id)
    {
        $rows_exists =  Classified::where(['id_user_sponsor' =>  $parent_id])->whereNotIn('user_id', array($parent_id))->exists();


        if ($rows_exists) {
            $rows = Classified::where(['id_user_sponsor' =>  $parent_id])
                ->whereNotIn('user_id', array($parent_id))
                ->join('users as u', 'classified.user_id', '=', 'u.id')
                ->select('classified.*', 'u.*', 'classified.position as classified_position')
                ->orderBy('classified.position', 'asc')
                ->get();

            $directs = [];

            foreach ($rows as $row) {
                $direct = $this->getFormatArray($row);

                array_push($directs, $direct);
            }
        } else {
            // Corrección: La estructura de nodo vacío debe estar dentro de un array si se espera un array de hijos.
            $directs = [];
        }
        return $directs;
    }

    public function getBinaryThreeDataInternal(): JsonResponse
    {
        try {
            $rootUserId = auth()->id();

            // 1. Traer todos los usuarios necesarios
            $users = User::leftJoin('classified as c', 'users.id', '=', 'c.user_id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.last_name',
                    'c.id_user_sponsor'
                )
                ->get();

            // 2. Traer datos del árbol (posición y padre)
            $classified = Classified::select('user_id', 'position', 'user_above')->get();

            // 3. Mapear usuarios por ID (usersMap)
            $usersMap = [];
            foreach ($users as $user) {
                $usersMap[$user->id] = $user;
            }

            // 4. Construir childrenMap (left / right)
            $childrenMap = [];
            foreach ($classified as $row) {
                if (!$row->user_above) {
                    continue;
                }

                if (!isset($childrenMap[$row->user_above])) {
                    $childrenMap[$row->user_above] = [
                        'left'  => [],
                        'right' => []
                    ];
                }

                if ((int)$row->position === 0) {
                    $childrenMap[$row->user_above]['left'][] = $row->user_id;
                }

                if ((int)$row->position === 1) {
                    $childrenMap[$row->user_above]['right'][] = $row->user_id;
                }
            }

            // 5. Construir el árbol recursivo
            $tree = $this->buildBinaryTreeRecursive(
                $rootUserId,
                $usersMap,
                $childrenMap
            );

            return response()->json($tree);

        } catch (\Throwable $e) {
            Log::error('Error árbol binario local', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al construir el árbol binario'
            ], 500);
        }
    }

    private function buildBinaryTreeRecursive($currentUserId, $usersMap, $childrenMap)
    {
        if (!$currentUserId || !isset($usersMap[$currentUserId])) {
            return null;
        }
    
        $user = $usersMap[$currentUserId];
        $children = $childrenMap[$currentUserId] ?? ['left' => [], 'right' => []];
    
        $leftChildId  = $children['left'][0] ?? null;
        $rightChildId = $children['right'][0] ?? null;
    
        return [
            'id'   => $user->id,
            'name' => trim($user->name . ' ' . ($user->last_name ?? '')),
            'id_user_sponsor' => $user->id_user_sponsor,
            'left'  => $this->buildBinaryTreeRecursive($leftChildId, $usersMap, $childrenMap),
            'right' => $this->buildBinaryTreeRecursive($rightChildId, $usersMap, $childrenMap),
        ];
    }

    public function getFormatArray($data)
    {
        $directs = $this->getDirects($data->id);
        return array('id' => $data->id, 'name' => $data->name, 'photo' => ParseUrl::contacAtrrS3($data->photo), 'position' => $data->classified_position, 'children' => $directs);
    }
}
