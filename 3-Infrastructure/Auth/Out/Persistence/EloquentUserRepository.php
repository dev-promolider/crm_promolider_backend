<?php
namespace Promolider\Infrastructure\Auth\Out\Persistence;

use Promolider\Domain\Auth\Ports\Out\UserRepositoryInterface;
use Promolider\Domain\Auth\Entities\User as UserEntity;
use App\Models\User as EloquentUser; // Depende de que tu modelo Eloquent exista en App\Models

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByUsername(string $username): ?UserEntity
    {
        $user = EloquentUser::where('username', $username)->first();
        
        if (!$user) {
            return null;
        }

        // Mapeamos el modelo Eloquent (Infraestructura) a la Entidad (Dominio)
        return new UserEntity(
            $user->id,
            $user->username,
            $user->password,
            $user->request ?? 0,
            $user->getRoleNames()->toArray()
        );
    }
}
