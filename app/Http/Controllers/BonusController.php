<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BonusController extends Controller
{
    public function index()
    {
        $data = [];

        $now = Carbon::now();
        $wallet_id = Wallet::where('user_id', auth()->user()->id)->first()->id;
        // $fast_cash_bonus = WalletMovements::where(['wallet_id' => $wallet_id])->whereMonth('created_at', $now->format('m'))->sum('amount');
        $last_batch = Option::lastBatch()->value;
        //=======Bonos acumulativos=========
        $fast_cash_bonus = WalletMovements::where('wallet_id', $wallet_id)->where('bonus_type_id', 1)->where('batch', $last_batch)->sum('amount');
        $producer_bonus = WalletMovements::where('wallet_id', $wallet_id)->where('bonus_type_id', 3)->where('batch', $last_batch)->sum('amount');
        $course_sales_bonus = WalletMovements::where('wallet_id', $wallet_id)->where('bonus_type_id', 2)->where('batch', $last_batch)->sum('amount');

        //=======Bonos de resumen mensual========
        $binary_bonus = WalletMovements::where('wallet_id', $wallet_id)->where('bonus_type_id', 4)->whereMonth('created_at', $now->format('m'))->sum('amount');
        $previus_batch = (int) $last_batch - 1;
        $rank_bonus = WalletMovements::where('wallet_id', $wallet_id)->where('bonus_type_id', 5)->where('batch', $previus_batch)->sum('amount');

        $expansion_bonus = WalletMovements::where('wallet_id', $wallet_id)->where('bonus_type_id', 6)->whereMonth('created_at', $now->format('m'))->sum('amount');


        $data['fast_cash_bonus'] = number_format((float)$fast_cash_bonus, 2, '.', '');
        $data['producer_bonus'] = number_format((float)$producer_bonus, 2, '.', '');
        $data['course_sale_bonus'] = number_format((float)$course_sales_bonus, 2, '.', '');
        $data['binary_bonus'] = number_format((float)$binary_bonus, 2, '.', '');
        $data['rank_bonus'] = number_format((float)$rank_bonus, 2, '.', '');
        $data['expansion_bonus'] = number_format((float)$expansion_bonus, 2, '.', '');


        return response()->json(['data' => $data]);

        //obtengo el bono de efectivo rapido
        // $fast_cash_bonus =  $user->wallets()->where('status',1)->sum('amount');
        // $fast_cash_bonus =  500;
        // //A medida que van aumentando otros tipos de bonos agregarlos al array asociativo
        // $data['fast_cash_bonus'] = $fast_cash_bonus;

    }
}

