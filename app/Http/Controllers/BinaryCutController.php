<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use App\Models\Option;
use App\Models\BinaryCutHistory;
use App\Models\Payment;
use App\Models\Point;
use App\Models\RankBinary;
use App\Models\RankBonus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

use Illuminate\Support\Facades\Http;

class BinaryCutController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:binarycut.index');
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        $users = User::qualifiedsAndActive();

        $ranks = RankBonus::select('id', 'vol_min', 'pack_max', 'active_direct', 'max_pay', 'monthly_bonus')
            ->get();

        return view('content.binarycut.index', compact('users'));
    }

    public function store()
    {
        $this->authorize('viewAny', auth()->user());
        Log::info('Iniciando el proceso de store para el corte binario (versión local)');
    
        try {
            DB::beginTransaction();
        
            $users = User::qualifiedsAndActive();
            Log::info('Usuarios calificados y activos obtenidos: ' . $users->count());
        
            $ranks = RankBonus::select('id', 'vol_min', 'pack_max', 'active_direct', 'max_pay', 'monthly_bonus', 'limit_generation')->get();
            Log::info('Rangos obtenidos: ' . $ranks->count());
        
            $batch = Option::where('description', 'batch')->first();
            $last_batch = (int) $batch->value;
            Log::info('Batch obtenido: ' . $last_batch);
        
            // Calcular puntos localmente en lugar de usar API externa
            Log::info('Calculando puntos binarios localmente...');
            $userPointsCache = $this->calculateBinaryPointsLocally($users);
            Log::info('Cálculo de puntos completado para ' . count($userPointsCache) . ' usuarios.');
        
            foreach ($users as $user) {
                Log::info("--- Procesando BONO BINARIO para usuario ID: {$user->id} ---");
            
                $userLeftPoints = $userPointsCache[$user->id]['left'] ?? 0;
                $userRightPoints = $userPointsCache[$user->id]['right'] ?? 0;
            
                $maxPoints = max($userLeftPoints, $userRightPoints);
                $minPoints = min($userLeftPoints, $userRightPoints);
                $sideMax = $userLeftPoints > $userRightPoints ? 0 : 1;
                Log::info("Puntos (calculados localmente) -> Usuario ID: {$user->id}, Izq: {$userLeftPoints}, Der: {$userRightPoints}. Pierna de pago: {$minPoints}");
            
                $my_rank = $this->setRanks($user->id, $minPoints, $ranks, $last_batch);
            
                $myWallet = Wallet::where('user_id', $user->id)->first();
                $maxTransfer = $my_rank->max_pay;
                $amountToTransfer = ($minPoints * 1) * ($user->accountType->pay_in_binary / 100);
            
                if ($amountToTransfer > $maxTransfer) {
                    Log::warning("El bono calculado ({$amountToTransfer}) excede el límite del rango ({$maxTransfer}). Se ajustará.");
                    $amountToTransfer = $maxTransfer;
                }
            
                if (method_exists($user, 'points')) {
                    $user->points()->where('status', 1)->update(['status' => 0]);
                    $user->points()->create([
                        'user_id' => $user->id,
                        'points' => $maxPoints - $minPoints,
                        'side' => $sideMax,
                        'reason' => "Binary cut"
                    ]);
                    Log::info("Puntos del usuario {$user->id} actualizados. Restantes: " . ($maxPoints - $minPoints));
                } else {
                    Log::warning("El método 'points()' no existe en el modelo User. No se actualizarán los puntos restantes para el usuario ID: {$user->id}.");
                }
            
                if ($amountToTransfer > 0) {
                    $movement = new WalletMovements();
                    $movement->wallet_id = $myWallet->id;
                    $movement->amount = $amountToTransfer;
                    $movement->type = 1;
                    $movement->reason = 'Bono binario';
                    $movement->batch = $last_batch;
                    $movement->bonus_type_id = 4;
                    $movement->save();
                }
            
                BinaryCutHistory::create([
                    'user_id' => $user->id,
                    'rank_id' => $my_rank->id,
                    'left_points' => $userLeftPoints,
                    'right_points' => $userRightPoints,
                    'transferred_amount' => $amountToTransfer,
                    'batch' => $last_batch
                ]);
            }
        
            Log::info("=== INICIO DE PAGO DE BONOS GENERACIONALES ===");
            foreach ($users as $user) {
                $myWallet = Wallet::where('user_id', $user->id)->first();
                if (!$myWallet) {
                    Log::warning("No se encontró wallet para el usuario ID: {$user->id}. Omitiendo bono generacional.");
                    continue;
                }
            
                $myRank = RankBinary::where('user_id', $user->id)->where('batch', $last_batch)->first();
            
                if ($myRank && $myRank->rank_id > 1) {
                    Log::info("Iniciando 'paymentsGeneration' para el usuario ID: {$user->id}");
                    $this->paymentsGeneration($user->id, $ranks, $myWallet->id, $last_batch, $userPointsCache);
                } else {
                    $rankIdInfo = $myRank ? $myRank->rank_id : 'ninguno';
                    Log::info("Usuario ID: {$user->id} no califica para bono generacional (Rango actual: {$rankIdInfo}).");
                }
            }
            Log::info("=== FIN DE PAGO DE BONOS GENERACIONALES ===");
        
            $new_batch_value = $last_batch + 1;
            $batch->value = $new_batch_value;
            $batch->save();
            Log::info("Proceso de corte finalizado. Batch actualizado a: " . $new_batch_value);
        
            DB::commit();
        
            return redirect()->route('binarycut.index')->withSuccess('Binary cut successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en el proceso de corte binario: ' . $e->getMessage() . ' en el archivo ' . $e->getFile() . ' en la línea ' . $e->getLine());
            return redirect()->route('binarycut.index')->withError('Error processing binary cut: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcula los puntos binarios localmente replicando la lógica de la API externa
     */
    private function calculateBinaryPointsLocally($users)
    {
        Log::info('Iniciando cálculo local de puntos binarios...');
        
        // Obtener todos los usuarios con sus datos necesarios
        $allUsers = User::select('id', 'name', 'last_name', 'expiration_membership_date', 'id_account_type')
            ->get();
        
        // Obtener puntos por tipo de cuenta
        $accountTypePoints = DB::table('account_type_points_money')
            ->select('account_type_id as id', 'points')
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
            
            // Verificar si está activo
            $expirationDate = $userData->expiration_membership_date ? 
                \Carbon\Carbon::parse($userData->expiration_membership_date) : null;
            $isActive = $expirationDate && $expirationDate->gt($now);
            
            if (!$isActive) {
                $results[$userId] = ['left' => 0, 'right' => 0];
                continue;
            }
            
            $directChildren = $childrenMap[$userId] ?? ['left' => [], 'right' => []];
            
            // Verificar calificación (tener al menos un directo en cada pierna)
            $hasDirectLeft = false;
            $hasDirectRight = false;
            
            foreach ($directChildren['left'] as $childId) {
                if (($sponsorMap[$childId] ?? null) == $userId) {
                    $hasDirectLeft = true;
                    break;
                }
            }
            
            foreach ($directChildren['right'] as $childId) {
                if (($sponsorMap[$childId] ?? null) == $userId) {
                    $hasDirectRight = true;
                    break;
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
                        // Solo sumar puntos si es directo patrocinado o si está calificado
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
                        // Solo sumar puntos si es directo patrocinado o si está calificado
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
            
            Log::debug("Usuario ID: {$userId} - Izq: {$totalLeftPoints}, Der: {$totalRightPoints}, Calificado: " . ($isQualified ? 'Sí' : 'No'));
        }
        
        Log::info('Cálculo local de puntos binarios completado.');
        return $results;
    }
    
    /**
     * Calcula recursivamente los puntos de una rama
     */
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

    public function myHistory()
    {
        $histories = BinaryCutHistory::where('user_id', auth()->id())
            ->with(['rank'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($histories);
    }

    public function userHistory($userId)
    {
        $this->authorize('viewAny', auth()->user());

        $histories = BinaryCutHistory::where('user_id', $userId)
            ->with(['rank', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('content.binarycut.user-history', compact('histories'));
    }

    public function getDirects($user, $batch)
    {
        Log::info("Buscando referidos directos para Usuario ID: {$user->id} que cobraron bono binario en el lote: {$batch}");

        $directs = User::join('wallet', 'users.id', '=', 'wallet.user_id')
            ->join('wallet_movements', 'wallet.id', '=', 'wallet_movements.wallet_id')
            ->where('users.id_referrer_sponsor', $user->id)
            ->where('wallet_movements.batch', $batch)
            ->where('wallet_movements.bonus_type_id', 4)
            ->select('users.id', 'users.username', 'wallet_movements.amount', 'wallet.id')
            ->get();

        Log::info("Se encontraron {$directs->count()} directos para Usuario ID: {$user->id}.");
        return $directs;
    }

    public function deliverBonusRank($total_bonus, $myWallet, $batch, $i, $bonus_percentage, $max_pay)
    {
        Log::info("--- Entrando a deliverBonusRank para Wallet ID: {$myWallet->id}, Generación: {$i} ---");
        Log::info("Total de bonos base: {$total_bonus}, Porcentaje a aplicar: {$bonus_percentage}%, Pago Máximo: {$max_pay}");

        $mount_to_pay = $total_bonus * $bonus_percentage / 100;
        Log::info("Monto calculado a pagar (antes de límite): {$mount_to_pay}");

        if ($mount_to_pay > 0) {
            $final_amount = $mount_to_pay;
            if ($mount_to_pay >= $max_pay) {
                $final_amount = $max_pay;
                Log::warning("El monto a pagar ({$mount_to_pay}) excede el máximo ({$max_pay}). Se pagará el máximo.");
            }

            $wallet_movement = new WalletMovements();
            $wallet_movement->wallet_id = $myWallet->id;
            $wallet_movement->amount = $final_amount;
            $wallet_movement->type = 1;
            $wallet_movement->status = 1;
            $wallet_movement->reason = "Bono generacional, " . $i . "° generación";
            $wallet_movement->bonus_type_id = 5;
            $wallet_movement->batch = $batch;
            $wallet_movement->save();
            Log::info("Bono de {$final_amount} guardado para Wallet ID: {$myWallet->id} en lote {$batch}.");

        } else {
            Log::info("No se paga bono para Generación {$i} porque el monto calculado es cero.");
        }
        Log::info("--- Saliendo de deliverBonusRank para Generación: {$i} ---");
    }

    public function repeatProcess($directs, $batch, $myWallet, $i, $bonus_percentage, $max_pay)
    {
        Log::info("==> Iniciando repeatProcess para Generación: {$i}, Wallet ID: {$myWallet->id}");
        $directsAux = [];
        $total_bonus = 0;
        foreach ($directs as $direct) {
            Log::info("Procesando descendientes del directo: " . print_r($direct, true));
            $users = $this->getDirects($direct, $batch);
            array_push($directsAux, $users);
            foreach ($users as $user) {
                $total_bonus += $user->amount;
                Log::info("Sumando bono de {$user->amount} del usuario ID {$user->id}. Total actual: {$total_bonus}");
            }
        }
        $this->deliverBonusRank($total_bonus, $myWallet, $batch, $i, $bonus_percentage, $max_pay);
        Log::info("==> Finalizando repeatProcess para Generación: {$i}.");
        return $directsAux;
    }

    public function rankBonusPay($user, $batch, $myWallet, $myRank)
    {
        Log::info("====================================================================");
        Log::info("Iniciando rankBonusPay para Usuario ID: {$user->id}, Lote: {$batch}, Rango: {$myRank->id}");

        $rank = $myRank->id;
        $max_pay = $myRank->max_pay;
        Log::info("Rango: {$rank}, Límite de pago por bono: {$max_pay}");

        $generational_bonuses = DB::table('generational_bonuses')->first();
        if (!$generational_bonuses) {
            Log::error("No se encontraron porcentajes de bonos generacionales en la tabla 'generational_bonuses'. El proceso se detendrá.");
            return;
        }
        Log::info("Porcentajes de bonos obtenidos: G1: {$generational_bonuses->g_1}%, G2: {$generational_bonuses->g_2}%");

        Log::info("--- Procesando Generación 1 ---");
        $directs = $this->getDirects($user, $batch);
        $total_bonus_g1 = $directs->sum('amount');
        Log::info("Suma total de bonos de G1: {$total_bonus_g1}");

        $this->deliverBonusRank(
            $total_bonus_g1,
            $myWallet,
            $batch,
            1,
            $generational_bonuses->g_1,
            $max_pay
        );

        if ($rank >= 2) {
            Log::info("--- Procesando Generación 2 (Rango {$rank} es suficiente) ---");
            $total_bonus_g2 = 0;

            foreach ($directs as $direct) {
                Log::info("Obteniendo referidos de segunda generación a través del directo ID: {$direct->id} ({$direct->username})");
                $second_generation = $this->getDirects($direct, $batch);
                $bonus_from_this_leg = $second_generation->sum('amount');
                $total_bonus_g2 += $bonus_from_this_leg;
                Log::info("Suma de bonos de esta rama: {$bonus_from_this_leg}. Total acumulado para G2: {$total_bonus_g2}");
            }

            Log::info("Suma total de bonos de G2: {$total_bonus_g2}");
            $this->deliverBonusRank(
                $total_bonus_g2,
                $myWallet,
                $batch,
                2,
                $generational_bonuses->g_2,
                $max_pay
            );
        } else {
            Log::info("El Rango {$rank} no es suficiente para cobrar la segunda generación. Proceso para G2 omitido.");
        }
        Log::info("Finalizando rankBonusPay para Usuario ID: {$user->id}");
        Log::info("====================================================================");
    }

    public function expansionBonus($user_id, $wallet_id, $last_batch)
    {

        $payments = Payment::join('users', 'users.id', '=', 'payments.user_id')
            ->join('account_type', 'account_type.id', '=', 'users.id_account_type')
            ->join('account_type_points_money', 'account_type_points_money.account_type_id', '=', 'users.id_account_type')
            ->where('id_user_sponsor', $user_id)->where('ex_bonus', 0)->whereBetween('account_type.id', [2, 4])
            ->selectRaw('count(account_type_points_money.account_type_id) as n_membership, account_type.account,account_type.id,account_type.price')
            ->groupBy('account_type_points_money.account_type_id')
            ->get();

        $movement = new WalletMovements();
        $movement->wallet_id = $wallet_id;
        $movement->type = 1;
        $movement->reason = 'Expansion Bonus';
        $movement->batch = $last_batch;

        for ($i = 0; $i < sizeof($payments); $i++) {
            if ($payments[$i]->id == 2 || $payments[$i]->id == 3) {
                if ($payments[$i]->n_membership == 4) {

                    $movement->amount = ($payments[$i]->price * 4) * (0.05);
                    $movement->save();
                }
                if ($payments[$i]->n_membership == 5) {
                    $movement->amount = ($payments[$i]->price * 5) * (0.06);
                    $movement->save();
                }
                if ($payments[$i]->n_membership == 6) {
                    $movement->amount = ($payments[$i]->price * 6) * (0.07);
                    $movement->save();
                }
                if ($payments[$i]->n_membership >= 7) {
                    $movement->amount = ($payments[$i]->price * 7) * (0.08);
                    $movement->save();
                }
            }
            ;

            if ($payments[$i]->id == 4) {
                if ($payments[$i]->n_membership == 4) {

                    $movement->amount = ($payments[$i]->price * 4) * (0.07);
                    $movement->save();
                }
                if ($payments[$i]->n_membership == 5) {
                    $movement->amount = ($payments[$i]->price * 5) * (0.08);
                    $movement->save();
                }
                if ($payments[$i]->n_membership == 6) {
                    $movement->amount = ($payments[$i]->price * 6) * (0.09);
                    $movement->save();
                }
                if ($payments[$i]->n_membership >= 7) {
                    $movement->amount = ($payments[$i]->price * 7) * (0.1);
                    $movement->save();
                }
            }
        }

        $payments = Payment::where('id_user_sponsor', $user_id)->where('ex_bonus', 0)
            ->update(['ex_bonus' => 1]);
    }


    public function rankBonus($user_id, $vol_min, $ranks)
    {
        Log::info("Calculando rank para usuario ID $user_id con volumen mínimo: $vol_min");

        $user = User::find($user_id);

        if (!$user) {
            Log::warning("Usuario ID $user_id no encontrado.");
            return $ranks[0];
        }

        $descendants = $user->allDescendants()->get();

        $allDescendantUsers = new Collection();
        $this->collectDescendants($descendants, $allDescendantUsers);

        $n_active_descendants = $allDescendantUsers->where('expiration_date', '>', now())
            ->where('expiration_membership_date', '>', now())
            ->count();

        Log::info("Descendientes activos (directos e indirectos): $n_active_descendants");

        $rank_condition = null;
        foreach ($ranks as $rank) {
            Log::info("Evaluando rango ID {$rank->id} => vol_min: {$rank->vol_min}, activos requeridos: {$rank->active_direct}");

            if ($vol_min >= $rank->vol_min && $n_active_descendants >= $rank->active_direct) {
                $rank_condition = $rank;
                Log::info("Rango preliminar posible: {$rank->id}");
            }
        }

        if (!$rank_condition) {
            Log::info("No se cumple ni el requisito mínimo de volumen o descendientes activos para ningún rango. Asignando rango más bajo ID {$ranks[0]->id}.");
            return $ranks[0];
        }

        if ($rank_condition->pack_max == 0) {
            Log::info("Rango {$rank_condition->id} no requiere membresías university. Asignando directamente.");
            return $rank_condition;
        }

        Log::info("El rango {$rank_condition->id} requiere {$rank_condition->pack_max} university. Verificando en descendientes.");

        $university_count = $allDescendantUsers->where('expiration_date', '>', now())
            ->where('expiration_membership_date', '>', now())
            ->where('id_account_type', 4)
            ->count();

        Log::info("El usuario ID $user_id tiene $university_count descendientes con membresía 'University' activa.");

        $best_valid_rank = null;
        foreach ($ranks as $rank) {
            if (
                $vol_min >= $rank->vol_min &&
                $n_active_descendants >= $rank->active_direct &&
                $university_count >= $rank->pack_max
            ) {
                $best_valid_rank = $rank;
                Log::info("Candidato válido: Rango {$rank->id} cumple todo (vol:{$rank->vol_min}, descendientes activos:{$rank->active_direct}, univ:{$rank->pack_max})");
            }
        }

        if ($best_valid_rank) {
            Log::info("Asignando el mejor rango posible que cumple todas las condiciones: {$best_valid_rank->id}");
            return $best_valid_rank;
        }

        Log::info("Ningún rango cumple los requisitos de university. Asignando rango más bajo ID {$ranks[0]->id}.");
        return $ranks[0];
    }

    private function collectDescendants($collection, &$allDescendantUsers)
    {
        foreach ($collection as $user) {
            $allDescendantUsers->push($user);
            if ($user->relationLoaded('allDescendants') && $user->allDescendants->isNotEmpty()) {
                $this->collectDescendants($user->allDescendants, $allDescendantUsers);
            }
        }
    }

    public function setRanks($user_id, $vol_min, $ranks, $last_batch)
    {
        $my_rank = $this->rankBonus($user_id, $vol_min, $ranks);
        Log::info("Asignando rango ID {$my_rank->id} al usuario ID $user_id para batch $last_batch");

        $new_rank = new RankBinary();
        $new_rank->user_id = $user_id;
        $new_rank->rank_id = $my_rank->id;
        $new_rank->batch = $last_batch;
        $new_rank->save();
        return $my_rank;
    }

    public function paymentsGeneration($user_id, $ranks, $wallet_id, $last_batch, $userPointsCache)
    {
        Log::info("====================================================================");
        Log::info("Iniciando paymentsGeneration para Usuario ID: $user_id, Lote: $last_batch");

        $rank_data = RankBinary::join('rank_bonus', 'rank_bonus.id', '=', 'rank_binary.rank_id')
            ->where('rank_binary.user_id', $user_id)
            ->orderBy('rank_binary.created_at', 'desc')
            ->select('rank_bonus.name', 'rank_bonus.limit_generation')
            ->first();

        if (!$rank_data) {
            Log::warning("No se encontró un rango para el usuario $user_id. No se pagan bonos generacionales.");
            return;
        }

        Log::info("Rango del usuario: {$rank_data->name}. Límite de pago: {$rank_data->limit_generation} generaciones.");

        $generation = 1;
        $users_in_generation = User::where('id_referrer_sponsor', $user_id)->where('expiration_date', '>', now())->pluck('id');

        while (count($users_in_generation) > 0 && $generation <= $rank_data->limit_generation) {
            Log::info("--- Procesando Generación {$generation} para Usuario ID: $user_id ---");

            $total_bonus_base = 0;
            foreach ($users_in_generation as $user_in_gen_id) {
                $pay_son = $this->paymentRoot($user_in_gen_id, $ranks, $userPointsCache);

                $percentage = 0;
                if ($generation >= 1 && $generation <= 3) {
                    $percentage = 0.05;
                } elseif ($generation == 4) {
                    $percentage = 0.03;
                } elseif ($generation == 5) {
                    $percentage = 0.02;
                } else {
                    $percentage = 0.01;
                }

                $total_bonus_base += $pay_son * $percentage;
            }
            Log::info("Total de bono para G{$generation}: {$total_bonus_base}");

            if ($total_bonus_base > 0) {

                $movement = new WalletMovements();
                $movement->wallet_id = $wallet_id;
                $movement->amount = $total_bonus_base;
                $movement->type = 1;
                $movement->batch = $last_batch;
                $movement->reason = 'Bono de ' . $generation . '° Generación';
                $movement->bonus_type_id = 5;
                $movement->save();
                Log::info("Bono de {$total_bonus_base} guardado para Wallet ID {$wallet_id}.");

            }
            $users_in_generation = User::whereIn('id_referrer_sponsor', $users_in_generation)->where('expiration_date', '>', now())->pluck('id');
            $generation++;
        }
        Log::info("====================================================================");
    }

    public function paymentRoot($id_son, $ranks, $userPointsCache)
    {
        Log::debug("------ Iniciando paymentRoot para el referido ID: {$id_son} ------");

        if (isset($userPointsCache[$id_son])) {
            $points_son_left = $userPointsCache[$id_son]['left'];
            $points_son_right = $userPointsCache[$id_son]['right'];
            Log::debug("Puntos del referido ID {$id_son} (CACHÉ): Izq.={$points_son_left}, Der.={$points_son_right}");
        } else {
            $points_son_left = 0;
            $points_son_right = 0;
            Log::warning("Referido ID {$id_son} no encontrado en caché. Se asumen 0 puntos.");
        }

        $minPoints = min($points_son_left, $points_son_right);
        Log::debug("Puntos mínimos (base para cálculo): {$minPoints}");

        if ($minPoints <= 0) {
            Log::debug("El referido no tiene puntos en la pierna menor. Bono base devuelto: 0");
            return 0;
        }

        $son_user = User::with('accountType')->find($id_son);
        if (!$son_user || !$son_user->accountType) {
            Log::error("No se pudo encontrar el usuario o su tipo de cuenta para el ID: {$id_son}.");
            return 0;
        }

        $pay_percentage = $son_user->accountType->pay_in_binary;
        $bonusAmount = $minPoints * ($pay_percentage / 100);
        Log::debug("Cálculo del bono: {$minPoints} pts * {$pay_percentage}% = {$bonusAmount}");

        $rank_user = $this->rankBonus($id_son, $minPoints, $ranks);
        $max_pay_limit = $rank_user->max_pay;
        Log::debug("Rango del referido: {$rank_user->id}, su límite de cobro es: {$max_pay_limit}");

        $finalAmount = $bonusAmount;
        if ($bonusAmount > $max_pay_limit) {
            $finalAmount = $max_pay_limit;
            Log::debug("El bono ({$bonusAmount}) excede el límite ({$max_pay_limit}). Se ajusta a {$finalAmount}.");
        }

        Log::debug("------ Finalizando paymentRoot para ID: {$id_son}. Monto de bono devuelto: {$finalAmount} ------");
        return $finalAmount;
    }

}
