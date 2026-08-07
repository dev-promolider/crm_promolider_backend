<?php

namespace Promolider\Application\Requests\Services;

use App\Models\AccountType;
use App\Models\AccountTypeDetail;
use App\Models\AccountTypeDetailHistory;
use App\Models\Classified;
use App\Models\User;

class NewUserService
{
    public function saveUserMembershipExpirationDate($user_id, $account_type_id)
    {
        $accountTypeDetail = new AccountTypeDetail();
        $accountTypeDetail->user_id = $user_id;
        return $this->saveUserMembershipHistory($accountTypeDetail, $account_type_id);
    }

    public function saveUserMembershipHistory($accountTypeDetail, $account_type_id)
    {
        $date = date("Y-m-d H:i:s");
        $account_type = AccountType::find($account_type_id);
        $accountTypeDetail->purchase_date = $date;

        if ($accountTypeDetail->id) {
            $accountTypeDetailHis = AccountTypeDetailHistory::where(['account_type_detail_id' => $accountTypeDetail->id, 'status' => 1])->first();

            if (($accountTypeDetail->expiration_date <= $date) or ($accountTypeDetailHis->account_type_id != $account_type_id)) {
                $accountTypeDetail->expiration_date = date("Y-m-d H:i:s", strtotime($date . "+" . $account_type->enrollment_duration . " month"));
            } else {
                $accountTypeDetail->expiration_date = date("Y-m-d H:i:s", strtotime($accountTypeDetail->expiration_date . "+" . $account_type->enrollment_duration . " month"));
            }

            if ($accountTypeDetailHis) {
                $accountTypeDetailHis->status = false;
                $accountTypeDetailHis->save();
            }
        } else {
            $accountTypeDetail->expiration_date = date("Y-m-d H:i:s", strtotime($date . "+" . $account_type->enrollment_duration . " month"));
        }

        $accountTypeDetail->status = true;

        if ($accountTypeDetail->save()) {
            $accountTypeDetailHistory = new AccountTypeDetailHistory();
            $accountTypeDetailHistory->account_type_id = $account_type_id;
            $accountTypeDetailHistory->account_type_detail_id = $accountTypeDetail->id;
            $accountTypeDetailHistory->purchase_date = $accountTypeDetail->purchase_date;
            $accountTypeDetailHistory->expiration_date = $accountTypeDetail->expiration_date;
            $accountTypeDetailHistory->status = $accountTypeDetail->status;
            $accountTypeDetailHistory->save();
        }
        return $accountTypeDetail;
    }

    public function getLastUserBeforeEmpty($startingUserId, $position = 'user_position_left')
    {
        $current = $startingUserId;
        $lastValid = null;

        while ($current) {
            $classified = Classified::where('user_id', $current)->first();
            if (!$classified)
                break;

            $lastValid = $classified->user_id;

            $next = null;
            if ($position === 'user_position_left') {
                $next = Classified::where('user_above', $current)->where('position', 0)->first();
            } else {
                $next = Classified::where('user_above', $current)->where('position', 1)->first();
            }

            if (!$next)
                break;
            $current = $next->user_id;
        }

        return $lastValid;
    }
}
