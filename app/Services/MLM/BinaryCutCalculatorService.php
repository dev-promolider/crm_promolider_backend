<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Classified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinaryCutCalculatorService
{
    /**
     * Calcula los puntos binarios localmente replicando la lógica de la API externa
     */
    public function calculateBinaryPointsLocally($users)
    {
        Log::info('BinaryCutCalculatorService: Iniciando cálculo local de puntos binarios...');
        
        // Obtener todos los usuarios con sus datos necesarios
        $allUsers = User::select('id', 'name', 'last_name', 'expiration_membership_date', 'expiration_date', 'id_account_type', 'request')
            ->get();
        
        // Obtener puntos por tipo de cuenta
        $accountTypePoints = DB::table('account_type_points_money')->select('account_type_id as id', 'points')
            ->get()
            ->keyBy('id');
        
        // Obtener estructura binaria (classified)
        $classifiedData = Classified::select('user_id', 'position', 'user_above', 'id_user_sponsor')
            ->get();
        
        $now = now();
        
        // Crear mapas para acceso eficiente
        $usersMap = $allUsers->keyBy('id');
        $pointsMap = $accountTypePoints->map(function($item) {
            return (float) $item->points;
        });
        
        // Construir mapa de hijos por posición
        $childrenMap = [];
        foreach ($classifiedData as $row) {
            $parentId = (int) $row->user_above;
            if (!$parentId) continue;
            
            if (!isset($childrenMap[$parentId])) {
                $childrenMap[$parentId] = ['left' => [], 'right' => []];
            }
            
            if ($row->position == 0) {
                $childrenMap[$parentId]['left'][] = $row->user_id;
            } elseif ($row->position == 1) {
                $childrenMap[$parentId]['right'][] = $row->user_id;
            }
        }
        
        // Crear mapa de patrocinios
        $sponsorMap = $classifiedData->keyBy('user_id')->map(function($item) {
            return $item->id_user_sponsor;
        });
        
        $results = [];
        
        foreach ($users as $user) {
            $userId = $user->id;
            $userData = $usersMap->get($userId);
            
            if (!$userData) {
                $results[$userId] = ['left' => 0, 'right' => 0];
                continue;
            }
            
            // Verificar si está activo (Request aprobado, Membresía y OPC)
            $expirationMembershipDate = $userData->expiration_membership_date ? 
                \Carbon\Carbon::parse($userData->expiration_membership_date) : null;
            $expirationOpcDate = $userData->expiration_date ? 
                \Carbon\Carbon::parse($userData->expiration_date) : null;
            
            $isRequestApproved = $userData->request == 2;
            $isMembershipActive = $expirationMembershipDate && $expirationMembershipDate->gt($now);
            $isOpcActive = empty($userData->expiration_date) || ($expirationOpcDate && $expirationOpcDate->gt($now));
            
            $isActive = $isRequestApproved && $isMembershipActive && $isOpcActive;
            
            if (!$isActive) {
                $results[$userId] = ['left' => 0, 'right' => 0];
                continue;
            }
            
            $directChildren = $childrenMap[$userId] ?? ['left' => [], 'right' => []];
            
            // Verificar calificación (tener al menos un directo activo en cada pierna)
            $hasDirectLeft = false;
            $hasDirectRight = false;
            
            foreach ($directChildren['left'] as $childId) {
                if (($sponsorMap[$childId] ?? null) == $userId) {
                    $childUser = $usersMap->get($childId);
                    if ($this->isUserActive($childUser, $now)) {
                        $hasDirectLeft = true;
                        break;
                    }
                }
            }
            
            foreach ($directChildren['right'] as $childId) {
                if (($sponsorMap[$childId] ?? null) == $userId) {
                    $childUser = $usersMap->get($childId);
                    if ($this->isUserActive($childUser, $now)) {
                        $hasDirectRight = true;
                        break;
                    }
                }
            }
            
            $isQualified = $hasDirectLeft && $hasDirectRight;
            
            $totalLeftPoints = 0;
            $totalRightPoints = 0;
            
            if ($isQualified) {
                // Calcular puntos de pierna izquierda
                foreach ($directChildren['left'] as $leftChildId) {
                    $leftChildUser = $usersMap->get($leftChildId);
                    if ($leftChildUser) {
                        if (($sponsorMap[$leftChildId] ?? null) == $userId) {
                            $totalLeftPoints += $pointsMap->get($leftChildUser->id_account_type, 0);
                        }
                        $totalLeftPoints += $this->calculateBranchPointsRecursive(
                            $leftChildId, $userId, true, $childrenMap, $usersMap, $pointsMap, $sponsorMap
                        );
                    }
                }
                
                // Calcular puntos de pierna derecha
                foreach ($directChildren['right'] as $rightChildId) {
                    $rightChildUser = $usersMap->get($rightChildId);
                    if ($rightChildUser) {
                        if (($sponsorMap[$rightChildId] ?? null) == $userId) {
                            $totalRightPoints += $pointsMap->get($rightChildUser->id_account_type, 0);
                        }
                        $totalRightPoints += $this->calculateBranchPointsRecursive(
                            $rightChildId, $userId, true, $childrenMap, $usersMap, $pointsMap, $sponsorMap
                        );
                    }
                }
            }
            
            $results[$userId] = [
                'left' => $totalLeftPoints,
                'right' => $totalRightPoints
            ];
            
            Log::debug("BinaryCutCalculatorService - Usuario ID: {$userId} - Izq: {$totalLeftPoints}, Der: {$totalRightPoints}, Calificado: " . ($isQualified ? 'Sí' : 'No'));
        }
        
        Log::info('BinaryCutCalculatorService: Cálculo local de puntos binarios completado.');
        return $results;
    }
    
    private function calculateBranchPointsRecursive($childId, $rootUserId, $isQualified, $childrenMap, $usersMap, $pointsMap, $sponsorMap)
    {
        $totalPoints = 0;
        $childrenOfChild = $childrenMap[$childId] ?? ['left' => [], 'right' => []];
        
        foreach (['left', 'right'] as $side) {
            foreach ($childrenOfChild[$side] as $grandChildId) {
                $grandChild = $usersMap->get($grandChildId);
                if ($grandChild) {
                    // Solo sumar puntos si el usuario raíz está calificado o si es patrocinado directo
                    if ($isQualified || ($sponsorMap[$grandChildId] ?? null) == $rootUserId) {
                        $totalPoints += $pointsMap->get($grandChild->id_account_type, 0);
                    }
                    $totalPoints += $this->calculateBranchPointsRecursive(
                        $grandChildId, $rootUserId, $isQualified, $childrenMap, $usersMap, $pointsMap, $sponsorMap
                    );
                }
            }
        }
        
        return $totalPoints;
    }

    private function isUserActive($user, $now) {
        if (!$user) return false;
        
        $expirationMembershipDate = $user->expiration_membership_date ? 
            \Carbon\Carbon::parse($user->expiration_membership_date) : null;
        $expirationOpcDate = $user->expiration_date ? 
            \Carbon\Carbon::parse($user->expiration_date) : null;
        
        $isRequestApproved = $user->request == 2;
        $isMembershipActive = $expirationMembershipDate && $expirationMembershipDate->gt($now);
        $isOpcActive = empty($user->expiration_date) || ($expirationOpcDate && $expirationOpcDate->gt($now));
        
        return $isRequestApproved && $isMembershipActive && $isOpcActive;
    }
}
