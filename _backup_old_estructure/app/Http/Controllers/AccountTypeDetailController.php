<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountTypeDetail;
use App\Models\AccountTypeDetailHistory;

class AccountTypeDetailController extends Controller
{
    public function getHistoryOfUserMembership(){
        $user = auth()->user();

        $accountTypeDetail = AccountTypeDetail::where('user_id',$user->id)
            ->join('account_type_detail_histories','account_type_details.id','=','account_type_detail_histories.account_type_detail_id')
            ->join('account_type','account_type_detail_histories.account_type_id','=','account_type.id')
            ->select('account_type_detail_histories.*','account_type.account as account_type_name')
            ->get();
        return $accountTypeDetail;
    }
}
