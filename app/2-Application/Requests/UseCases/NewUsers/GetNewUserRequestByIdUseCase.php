<?php

namespace Promolider\Application\Requests\UseCases\NewUsers;

use App\Models\User;

class GetNewUserRequestByIdUseCase
{
    public function execute($id)
    {
        $data = User::where('id', $id)
            ->get();

        return count($data) > 0 ? $data[0] : null;
    }
}
