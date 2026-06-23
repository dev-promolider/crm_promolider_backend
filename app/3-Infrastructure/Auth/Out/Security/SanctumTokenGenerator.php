<?php
namespace Promolider\Infrastructure\Auth\Out\Security;

use Promolider\Domain\Auth\Ports\Out\TokenGeneratorInterface;
use Promolider\Domain\Auth\Entities\User;
use App\Models\User as EloquentUser;

class SanctumTokenGenerator implements TokenGeneratorInterface
{
    public function generateTokenForUser(User $user): string
    {
        // En Laravel, necesitamos el modelo Eloquent real para generar el token Sanctum
        $eloquentUser = EloquentUser::find($user->id);
        
        // Creamos el token
        return $eloquentUser->createToken('authToken')->plainTextToken;
    }
}
