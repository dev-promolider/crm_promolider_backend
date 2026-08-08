<?php

namespace Promolider\Application\Requests\UseCases\NewUsers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Classified;
use App\Models\AccountType;
use App\Models\UserDailyQuizz;
use App\Models\UserClassroomPoint;
use App\Models\Notifications;
use App\Models\Option;
use App\Models\AccountTypePointsMoney;
use App\Models\Point;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Requests\Services\NewUserService;
use Exception;

class UpdateNewUserRequestUseCase
{
    private $newUserService;

    public function __construct(NewUserService $newUserService)
    {
        $this->newUserService = $newUserService;
    }

    public function execute($id, $status, $id_referrer_sponsor)
    {
        $user = User::findOrFail($id);
        
        if ($status == 3) {
            $user->request = $status;
            $user->update();
            return;
        }

        if ($status == 2) {
            $account_type = AccountType::find($user->id_account_type);
            $id_user = $user->id;

            if (!Wallet::where('user_id', $id_user)->exists()) {
                Wallet::create(['user_id' => $id_user, 'status' => 1]);
            }

            if (!UserDailyQuizz::where('id_user', $id_user)->exists()) {
                $user_daily_quizz = new UserDailyQuizz();
                $user_daily_quizz->id_user = $id_user;
                $user_daily_quizz->passed_quizz = 0;
                $user_daily_quizz->save();
            }

            if (!UserClassroomPoint::where('id_user', $id_user)->exists()) {
                $user_classroom_point = new UserClassroomPoint();
                $user_classroom_point->id_user = $id_user;
                $user_classroom_point->total_points = 0;
                $user_classroom_point->save();
            }

            $this->newUserService->saveUserMembershipExpirationDate($id_user, $account_type->id);

            if (!Classified::where('user_id', $id_user)->exists()) {
                $user_referrer_position = User::select('username', 'position')->where('id', $id_referrer_sponsor)->first();
                $user->position = $user->position == 0 ? 1 : 0;
                $position = $user_referrer_position->position == 0 ? 'user_position_left' : 'user_position_right';
                
                $user_above = $this->newUserService->getLastUserBeforeEmpty($id_referrer_sponsor, $position);

                $fieldsClassifieds = [
                    'user_id' => $id_user,
                    'id_user_sponsor' => $id_referrer_sponsor,
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
                $notification->id_receiver = $id_referrer_sponsor;
                $notification->title = "Registro de Nuevo Afiliado";
                $notification->body = $user->name . ' ' . $user->last_name . ' se acaba de registrar con tu enlace';
                $notification->type = 1;
                $notification->save();
            }

            $this->updateRequest($status, $id_user);
        }
    }

    private function updateRequest($status, $id)
    {
        Log::info("updateRequest iniciado", ['status' => $status, 'id' => $id]);
    
        try {
            if ($status == 2) { 
                DB::transaction(function () use ($status, $id) {
                    $user = User::find($id);
                    if (!$user) {
                        throw new Exception("Usuario no encontrado con ID: {$id}");
                    }
                
                    $user->request = $status;
                    $user->expiration_date = date('Y-m-d H:i:s', strtotime('+30 days'));
                    $user->expiration_membership_date = date('Y-m-d H:i:s', strtotime('+365 days'));
                    $user->save();
                
                    $fullName = $user->name;
                    $membersip = $user->id_account_type;
                
                    $last_batch = Option::lastBatch();
                    $last_batch = (int) $last_batch->value;
                
                    $atm = AccountTypePointsMoney::where('account_type_id', $user->id_account_type)->first();
                    if (!$atm) {
                        throw new Exception("Configuración de puntos no encontrada para account_type: {$user->id_account_type}");
                    }
                
                    $account_type = AccountType::where('id', $user->id_account_type)->first();
                    if (!$account_type) {
                        throw new Exception("Tipo de cuenta no encontrado: {$user->id_account_type}");
                    }
                
                    $classified_user = Classified::where('user_id', $id)->first();
                    if (!$classified_user) {
                        throw new Exception("Clasificación de usuario no encontrada para ID: {$id}");
                    }
                
                    $save_position_branch = $classified_user->position ?? null;
                    $aux = false;
                
                    if ($membersip != 5 && $membersip != 6) {
                        $tmp_id = $classified_user->user_id ?? null;
                        $iteration_count = 0;
                        $max_iterations = 100;
                    
                        while ($aux == false && $tmp_id && $iteration_count < $max_iterations) {
                            $iteration_count++;
                            
                            $user_data = Classified::where('user_id', $tmp_id)->first();
                            
                            if (!$user_data) {
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
                            } elseif (isset($classified_user->id_user_sponsor) && $classified_user->id_user_sponsor == $user_data->user_id) {
                                Point::create([
                                    'user_id' => $user->id,
                                    'sponsor_id' => $classified_user->id_user_sponsor,
                                    'points' => $atm->points,
                                    'side' => $save_position_branch,
                                    'reason' => "Binary Team Points, " . $fullName . " Affiliation"
                                ]);
                            }
                        
                            $save_position_branch = $user_data->position ?? null;
                            $tmp_id = $user_data->user_above;
                        }
                    }
                
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
                                    }
                                }
                            }
                        }
                    }
                });
                
            } else {
                DB::transaction(function () use ($status, $id) {
                    $user = User::find($id);
                    if (!$user) {
                        throw new Exception("Usuario no encontrado con ID: {$id}");
                    }
                    $user->request = $status;
                    $user->save();
                });
            }
        } catch (Exception $e) {
            Log::error("Error en updateRequest", [
                'user_id' => $id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
