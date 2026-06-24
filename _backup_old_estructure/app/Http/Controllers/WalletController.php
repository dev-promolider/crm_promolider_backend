<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct(){
        $this->middleware('can:report-wallets');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotalWalletUsers()
    {
        $user = auth()->user();
        $this->authorize('viewAny', $user);
        $wallets = Wallet::groupBy('user_id')
        ->selectRaw('sum(AMOUNT) as available, user_id')->where('status','>',0)->with('user')->get();
        return JsonResource::collection($wallets);
    }

    public function getWalletForUser($username)
    {
        $user = User::where('username',$username)->first();
        $this->authorize('view', $user);
        $wallets = $user->wallets->where('status','>',1);
        return JsonResource::collection($wallets);
    }
}
