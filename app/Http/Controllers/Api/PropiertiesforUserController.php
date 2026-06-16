<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseFormat;
use App\Http\Controllers\Controller;

class PropiertiesforUserController extends Controller
{
    use ResponseFormat;

    /**
     * Show user's data
     * @return \Illuminate\Http\Response
     */
    public function getPropierties()
    {
        $user = User::find(auth()->user()->id);
        $totalPayments = $user->paymentsSponsor()->sum('amount');
        $totalCourses = $user->courses()->count();
        $accountType = $user->accountType->account;
        $totalClients  = User::myClients($user->id)->count();
        $role = $user->getRoleNames();
        $role = $role[0];
        
        $data = [
            'totalPayments' => $totalPayments,
            'totalCourses' => $totalCourses,
            'accountType' => $accountType,
            'totalClients' => $totalClients,
            'role' => $role
        ];
        return $this->responseOk('',$data);
    }
}
