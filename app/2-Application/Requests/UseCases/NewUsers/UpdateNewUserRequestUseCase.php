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
use App\Services\MLM\AffiliationRewardsService;
use Promolider\Application\Requests\Services\NewUserService;
use Exception;

class UpdateNewUserRequestUseCase
{
    private $newUserService;
    private $affiliationRewards;

    public function __construct(
        NewUserService $newUserService,
        AffiliationRewardsService $affiliationRewards
    ) {
        $this->newUserService = $newUserService;
        $this->affiliationRewards = $affiliationRewards;
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
                $referrerPosition = $user_referrer_position->position ?? 0;
                $position = $referrerPosition == 0 ? 'user_position_left' : 'user_position_right';
                
                $user_above = $this->newUserService->getLastUserBeforeEmpty($id_referrer_sponsor, $position);

                $fieldsClassifieds = [
                    'user_id' => $id_user,
                    'id_user_sponsor' => $id_referrer_sponsor,
                    'binary_sponsor' => $user_referrer_position->username,
                    'position' => $referrerPosition,
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

                    // Puntos binarios de la afiliacion y bono de inicio rapido del
                    // patrocinador. Vive en AffiliationRewardsService porque el alta por
                    // pasarela y el alta gratuita necesitan exactamente lo mismo.
                    $this->affiliationRewards->distribute($user->id);
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