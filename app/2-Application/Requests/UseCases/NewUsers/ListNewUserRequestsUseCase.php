<?php

namespace Promolider\Application\Requests\UseCases\NewUsers;

use App\Models\User;

class ListNewUserRequestsUseCase
{
    public function execute()
    {
        $users = User::with(['country', 'payments' => function($q) {
            $q->orderBy('created_at', 'desc');
        }])->where('request', 1)->get();

        return $users->map(function ($user) {
            $payment = $user->payments->first();
            $transaction_id = $payment ? $payment->operation_number : 'N/A';
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'country' => $user->country ? $user->country->name : 'N/A',
                'created_at' => $user->created_at,
                'formatted_created_at_string' => $user->created_at ? (clone $user->created_at)->format('Y-m-d H:i') : 'N/A',
                'transaction_id' => $transaction_id,
                'id_referrer_sponsor' => $user->id_referrer_sponsor,
            ];
        });
    }
}
