<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Point;
use App\Models\Wallet;
use App\Models\WalletMovements;
use App\Models\Classified;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccountTypePointsMoney;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Notifications;
use App\Models\BadgeDetail;
use App\Models\Option;
use App\Models\UserClassroomPoint;
use App\Models\UserDailyQuizz;
use Illuminate\Support\Facades\Log;

class UserRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:new-users');
    }
    public function index()
    {
        $this->isAdmin(auth()->user());

        $all_user_requesting = User::with('accountType', 'paymentsClient')
            ->where('request', 1)
            ->get();

        return JsonResource::collection($all_user_requesting);
    }

    protected function isAdmin(User $user)
    {
        if ($user->id != 1) {
            throw new HttpException(422, 'Este usuario no es el administrador');
        }
    }

    // get user by id
    public function getUserById($id)
    {
        $data = User::with('sponsor')
            ->with('paymentsClient', function ($q) {
                $q->with('paymentMethod');
            })
            ->with('documentType')
            ->where('id', $id)
            ->get();

        return response()->json($data[0]);
    }


    public function updateUnverifiedRequest(Request $request) {
        $user = User::findOrFail($request->id);
        if (!Classified::where('user_id', $user->id)->exists()) {
            if ($request->status == 3) {
                $user->request = $request->status;
                $user->update();
            } else if ($request->status == 2) {
                $account_type = AccountType::find($user->id_account_type);
                $id_user = $user->id;

                Wallet::create(['user_id' => $id_user, 'status' => 1]);

                $user_daily_quizz = new UserDailyQuizz();
                $user_daily_quizz->id_user = $id_user;
                $user_daily_quizz->passed_quizz = 0;
                $user_daily_quizz->save();

                $user_referrer_position = User::select('username', 'position')->where('id', $user->id_referrer_sponsor)->first();
                app(UserController::class)->saveUserMembershipExpirationDate($id_user, $account_type->id);

                $user_classroom_point = new UserClassroomPoint();
                $user_classroom_point->id_user = $id_user;
                $user_classroom_point->total_points = 0;
                $user_classroom_point->save();

                // Corregir la asignación de posición
                $user->position = $user->position == 0 ? 1 : 0;

                $position = $user_referrer_position->position == 0 ? 'user_position_left' : 'user_position_right';
                $user_above = app(UserController::class)->getLastUserBeforeEmpty($request["id_referrer_sponsor"], $position);

                $fieldsClassifieds = [
                    'user_id' => $id_user,
                    'id_user_sponsor' => $request["id_referrer_sponsor"],
                    'binary_sponsor' => $user_referrer_position->username,
                    'position' => $user->position,
                    'classification' => 16,
                    'status' => '0',
                    'authorized' => '0',
                    'user_above' => $user_above,
                ];

                Classified::create($fieldsClassifieds);

                $notification = new Notifications();
                $notification->id_generator = $id_user;
                $notification->id_receiver = $request["id_referrer_sponsor"];
                $notification->title = "Registro de Nuevo Afiliado";
                $notification->body = $user->name . ' ' . $user->last_name . ' se acaba de registrar con tu enlace';
                $notification->type = 1;
                $notification->save();

                $this->updateRequest($request->status, $id_user);
            }
        }
    }

    public function updateRequest($status, $id)
    {
        Log::info("updateRequest iniciado", ['status' => $status, 'id' => $id]);
    
        try {
            if ($status == 2) { 
                DB::transaction(function () use ($status, $id) {
                    Log::info("Transaction iniciada para status=2", ['id' => $id]);
                
                    $user = User::find($id);
                    if (!$user) {
                        Log::error("Usuario no encontrado", ['id' => $id]);
                        throw new \Exception("Usuario no encontrado con ID: {$id}");
                    }
                
                    $user->request = $status;
                    $user->save();
                    Log::info("Usuario actualizado", ['user_id' => $user->id, 'status' => $status]);
                
                    $fullName = $user->name;
                    $membersip = $user->id_account_type;
                
                    // Batch para el historial de la billetera según corte binario
                    $last_batch = Option::lastBatch();
                    $last_batch = (int) $last_batch->value;
                    Log::info("Último batch obtenido", ['batch' => $last_batch]);
                
                    $atm = AccountTypePointsMoney::where('account_type_id', $user->id_account_type)->first();
                    if (!$atm) {
                        Log::error("No se encontró AccountTypePointsMoney", ['account_type_id' => $user->id_account_type]);
                        throw new \Exception("Configuración de puntos no encontrada para account_type: {$user->id_account_type}");
                    }
                
                    $account_type = AccountType::where('id', $user->id_account_type)->first();
                    if (!$account_type) {
                        Log::error("No se encontró AccountType", ['id' => $user->id_account_type]);
                        throw new \Exception("Tipo de cuenta no encontrado: {$user->id_account_type}");
                    }
                
                    $classified_user = Classified::where('user_id', $id)->first();
                    if (!$classified_user) {
                        Log::error("No se encontró Classified para el usuario", ['user_id' => $id]);
                        throw new \Exception("Clasificación de usuario no encontrada para ID: {$id}");
                    }
                
                    $save_position_branch = $classified_user->position ?? null;
                
                    Log::info("Datos de usuario clasificado obtenidos", [
                        'classified_user' => $classified_user,
                        'position_branch' => $save_position_branch
                    ]);
                
                    $aux = false;
                
                    if ($membersip != 5 && $membersip != 6) {
                        $tmp_id = $classified_user->user_id ?? null;
                        $iteration_count = 0;
                        $max_iterations = 100; // Prevenir loops infinitos
                    
                        while ($aux == false && $tmp_id && $iteration_count < $max_iterations) {
                            $iteration_count++;
                            
                            $user_data = Classified::where('user_id', $tmp_id)->first();
                            
                            // VALIDACIÓN CRÍTICA: Verificar que $user_data existe
                            if (!$user_data) {
                                Log::warning("No se encontró clasificación para user_id, terminando loop", [
                                    'tmp_id' => $tmp_id,
                                    'iteration' => $iteration_count
                                ]);
                                break;
                            }
                        
                            $aux = $user_data->user_above == null ? true : false;
                            $user_status = User::find($tmp_id);
                        
                            if ($user_status && $user_status->active && $user_status->qualified && $user_status->membershipActive) {
                                Point::create([
                                    'user_id' => $user->id,
                                    'sponsor_id' => $user_data->user_id,
                                    'points' => $atm->points,
                                    'side' => $save_position_branch,
                                    'reason' => "Binary Team Points, " . $fullName . " Affiliation"
                                ]);
                                Log::info("Puntos binarios asignados", [
                                    'user_id' => $user->id, 
                                    'sponsor_id' => $user_data->user_id
                                ]);
                            } elseif (isset($classified_user->id_user_sponsor) && $classified_user->id_user_sponsor == $user_data->user_id) {
                                Point::create([
                                    'user_id' => $user->id,
                                    'sponsor_id' => $classified_user->id_user_sponsor,
                                    'points' => $atm->points,
                                    'side' => $save_position_branch,
                                    'reason' => "Binary Team Points, " . $fullName . " Affiliation"
                                ]);
                                Log::info("Puntos binarios asignados (sponsor directo)", ['user_id' => $user->id]);
                            }
                        
                            $save_position_branch = $user_data->position ?? null;
                            $tmp_id = $user_data->user_above;
                        }
                    
                        if ($iteration_count >= $max_iterations) {
                            Log::warning("Se alcanzó el máximo de iteraciones en el loop de clasificación", [
                                'user_id' => $id,
                                'iterations' => $iteration_count
                            ]);
                        }
                    }
                
                    // Bono de efectivo rápido
                    if ($membersip != 5 && $membersip != 6) {
                        if (isset($user->id_referrer_sponsor) && $user->id_referrer_sponsor != 1) {
                            $id_account_type_sponsor = User::select('id_account_type')
                                ->where('id', $user->id_referrer_sponsor)
                                ->first();
                            
                            if ($id_account_type_sponsor) {
                                $fast_cash_sponsor = AccountType::select('fast_cash_bonus')
                                    ->where('id', $id_account_type_sponsor->id_account_type)
                                    ->first();
                                
                                if ($fast_cash_sponsor) {
                                    $walletParentDirect = Wallet::where('user_id', $user->id_referrer_sponsor)->first();
                                    
                                    if ($walletParentDirect) {
                                        $movement = new WalletMovements();
                                        $movement->wallet_id = $walletParentDirect->id;
                                        $movement->amount = $account_type->price * ($fast_cash_sponsor->fast_cash_bonus / 100);
                                        $movement->type = 1;
                                        $movement->batch = $last_batch;
                                        $movement->bonus_type_id = 1;
                                        $movement->reason = 'Bono de efectivo rápido de ' . $user->username;
                                        $movement->save();
                                    
                                        Log::info("Bono de efectivo rápido asignado", [
                                            'user_id' => $user->id, 
                                            'sponsor_id' => $user->id_referrer_sponsor
                                        ]);
                                    } else {
                                        Log::warning("Wallet del sponsor no encontrado", [
                                            'sponsor_id' => $user->id_referrer_sponsor
                                        ]);
                                    }
                                } else {
                                    Log::warning("Fast cash bonus no encontrado", [
                                        'account_type_id' => $id_account_type_sponsor->id_account_type
                                    ]);
                                }
                            } else {
                                Log::warning("Sponsor no encontrado", ['sponsor_id' => $user->id_referrer_sponsor]);
                            }
                        }
                    }
                });
                
            } else {
                DB::transaction(function () use ($status, $id) {
                    $user = User::find($id);
                    if (!$user) {
                        Log::error("Usuario no encontrado para cambio de status", ['id' => $id]);
                        throw new \Exception("Usuario no encontrado con ID: {$id}");
                    }
                    $user->request = $status;
                    $user->save();
                    Log::info("Usuario actualizado con nuevo status", [
                        'user_id' => $user->id, 
                        'status' => $status
                    ]);
                });
            }
            
            Log::info("updateRequest completado exitosamente", ['user_id' => $id, 'status' => $status]);
            
        } catch (\Exception $e) {
            Log::error("Error en updateRequest", [
                'user_id' => $id,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-lanzar la excepción para que la transacción haga rollback
            throw $e;
        }
    }

    public function validateIfUserHasBadge($badge_id, $user_id)
    {
        $bool = BadgeDetail::where(['user_id' => $user_id, 'badge_id' => $badge_id])->exists();
        return $bool;
    }

    public function notification($id_user, $title, $body)
    {
        try {
            DB::beginTransaction();
            $notification = new Notifications();
            $notification->id_generator = $id_user;
            $notification->id_receiver =  $id_user;
            $notification->title = $title;
            $notification->body = $body;
            $notification->type = 3; # Compra de cursos
            $notification->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
