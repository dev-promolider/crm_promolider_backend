<?php

namespace Promolider\Application\Requests\UseCases\NewUsers;

use App\Models\User;

class ListNewUserRequestsUseCase
{
    public function execute()
    {
        return User::where('request', 1)
            ->get();
    }
}
