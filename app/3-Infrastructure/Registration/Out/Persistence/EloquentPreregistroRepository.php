<?php
namespace Promolider\Infrastructure\Registration\Out\Persistence;

use Promolider\Domain\Registration\Entities\Preregistro as PreregistroEntity;
use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;
use App\Models\Preregistro as EloquentPreregistro;
use App\Models\PreregistroLink;
use App\Models\User;
use App\Models\UnverifiedUser;
use DateTime;

class EloquentPreregistroRepository implements PreregistroRepositoryInterface
{
    public function findByEmail(string $email): ?PreregistroEntity
    {
        $record = EloquentPreregistro::where('correo', $email)->first();

        if (!$record) return null;

        return $this->mapToEntity($record);
    }

    public function findByToken(string $token): ?PreregistroEntity
    {
        $record = EloquentPreregistro::where('access_token', $token)->first();

        if (!$record) return null;

        return $this->mapToEntity($record);
    }

    public function findById(int $id): ?PreregistroEntity
    {
        $record = EloquentPreregistro::find($id);

        if (!$record) return null;

        return $this->mapToEntity($record);
    }

    public function create(array $data): PreregistroEntity
    {
        $data['access_token'] = \Illuminate\Support\Str::random(48);
        $data['token_expires_at'] = now()->addHours(72);

        $record = EloquentPreregistro::create($data);

        return $this->mapToEntity($record, true);
    }

    public function update(int $id, array $data): void
    {
        EloquentPreregistro::where('id', $id)->update($data);
    }

    public function checkDuplicate(string $field, string $value): bool
    {
        $fieldMap = [
            'email'        => 'email',
            'username'     => 'username',
            'nro_document' => 'nro_document',
            'phone'        => 'phone',
        ];

        $dbField = $fieldMap[$field] ?? $field;

        return User::where($dbField, $value)->exists();
    }

    public function emailExistsAsUser(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function getPreregistroConfig(string $username): array
    {
        $linkConfig = PreregistroLink::where('username', $username)->first();

        if (!$linkConfig) {
            throw new \Exception('Link de preregistro no configurado.', 404);
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            throw new \Exception('Usuario no encontrado.', 404);
        }

        return [
            'username'           => $username,
            'lado'               => $linkConfig->lado,
            'landing'            => $linkConfig->landing,
            'nombre_referidor'   => $user->name,
            'apellido_referidor' => $user->last_name,
            'correo_referidor'   => $user->email,
            'telefono_referidor' => $user->phone,
        ];
    }

    public function getPendingUnverifiedId(string $email, ?int $preregistroId): ?int
    {
        $query = UnverifiedUser::where(function ($q) use ($email, $preregistroId) {
            $q->where('data->email', $email)
              ->orWhere('data', 'like', '%"email":"' . $email . '"%')
              ->orWhere('data', 'like', '%"email": "' . $email . '"%');

            if ($preregistroId) {
                $q->orWhere('data->preregistro_id', (int) $preregistroId)
                  ->orWhere('data', 'like', '%"preregistro_id":' . (int) $preregistroId . '%')
                  ->orWhere('data', 'like', '%"preregistro_id": ' . (int) $preregistroId . '%');
            }
        });

        $user = $query->latest()->first();
        return $user ? $user->id : null;
    }

    /**
     * Mapea el modelo Eloquent a la entidad de dominio.
     */
    private function mapToEntity(EloquentPreregistro $record, bool $isNew = false): PreregistroEntity
    {
        $status = 0;

        if (!$isNew) {
            // Determinar status basado en UnverifiedUser (Optimización: evitar data->email para prevenir timeout)
            $hasPendingPayment = UnverifiedUser::where('data', 'like', '%"email":"' . $record->correo . '"%')->exists();

            if ($hasPendingPayment) {
                $status = 1;
            }
        }

        return new PreregistroEntity(
            id:               $record->id,
            name:             $record->nombres ?? '',
            email:            $record->correo ?? '',
            whatsapp:         $record->whatsapp ?? '',
            referrerId:       0,
            accessToken:      $record->access_token,
            tokenExpiresAt:   $record->token_expires_at ? new DateTime($record->token_expires_at) : null,
            status:           $status,
            referrerUsername:  $record->referrer_username,
            accountType:      null,
            side:             $record->lado,
        );
    }
}
