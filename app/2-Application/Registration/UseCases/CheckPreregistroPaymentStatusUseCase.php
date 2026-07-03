<?php
namespace Promolider\Application\Registration\UseCases;

use App\Models\Preregistro;
use App\Models\User;
use App\Models\UnverifiedUser;
use Exception;

class CheckPreregistroPaymentStatusUseCase
{
    public function execute(string $email): array
    {
        // 1. ¿Ya es usuario registrado?
        $user = User::where('email', $email)->first();
        if ($user) {
            return [
                'status'  => 'registered',
                'message' => 'El correo ya tiene una cuenta activa.',
                'email'   => $email,
            ];
        }

        // 2. ¿Tiene preregistro?
        $preregistro = Preregistro::where('correo', $email)->latest()->first();
        if (! $preregistro) {
            return [
                'status'  => 'not_found',
                'message' => 'No se encontró ningún preregistro con ese correo.',
                'email'   => $email,
            ];
        }

        // 3. ¿Tiene pago pendiente (UnverifiedUser)?
        $unverified = $this->findPendingPreregistroUser($email, $preregistro->id);
        if ($unverified) {
            return [
                'status'         => 'payment_pending',
                'message'        => 'El preregistro tiene un pago iniciado pero no confirmado.',
                'email'          => $email,
                'preregistro_id' => $preregistro->id,
                'openpay_order'  => $unverified->openpay_order_id,
            ];
        }

        // 4. Preregistro existe pero sin pago iniciado
        return [
            'status'         => 'preregistered',
            'message'        => 'El preregistro existe pero aún no ha iniciado el pago.',
            'email'          => $email,
            'preregistro_id' => $preregistro->id,
        ];
    }

    private function findPendingPreregistroUser(string $correo, ?int $preregistroId = null): ?UnverifiedUser
    {
        return UnverifiedUser::where(function ($query) use ($correo, $preregistroId) {
            // Optimización: Solo usamos LIKE para evitar timeout por JSON_EXTRACT
            $query->where('data', 'like', '%"email":"' . $correo . '"%')
                  ->orWhere('data', 'like', '%"email": "' . $correo . '"%');

            if ($preregistroId) {
                $query->orWhere('data', 'like', '%"preregistro_id":' . (int) $preregistroId . '%')
                      ->orWhere('data', 'like', '%"preregistro_id": ' . (int) $preregistroId . '%');
            }
        })->latest()->first();
    }
}
