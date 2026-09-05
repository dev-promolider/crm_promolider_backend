<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Entities\RegistrationUser;
use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;
use Promolider\Domain\Registration\Ports\Out\NotificationServiceInterface;
use Exception;

class CreateRegisteredUserUseCase
{
    public function __construct(
        private RegistrationRepositoryInterface $registrationRepository,
        private NotificationServiceInterface $notificationService
    ) {}

    /**
     * Crea un usuario completo con todas las entidades asociadas (Wallet, Classified, Payment, Notification).
     * Soporta tanto registro de pago como gratuito (free tier) mediante la entidad RegistrationUser.
     * 
     * Lógica extraída de: UserController::Create() + UserController::CreateFree()
     * 
     * Decisión: CreateFree se integra aquí con el flag isFreeRegistration (la lógica es ~90% idéntica).
     * La diferencia es: free tier tiene request=2 siempre, expiration=+10 years, sin pago.
     */
    public function execute(RegistrationUser $user, array $paymentData = [], string $rawPassword = ''): array
    {
        // 1. Obtener avatar por defecto
        $defaultAvatar = $this->registrationRepository->getDefaultAvatar();
        if ($defaultAvatar) {
            $user->photo = 'images/' . $defaultAvatar;
        }

        // 2. Crear el usuario
        $userId = $this->registrationRepository->createUser($user);

        // 3. Eliminar enlace compartido del patrocinador (una vez usado)
        $this->registrationRepository->deleteSharedLink($user->idReferrerSponsor);

        // 4. Asignar rol
        if ($user->userType) {
            $this->registrationRepository->assignRole($userId, $user->userType);
        }

        // 5. Crear billetera
        $this->registrationRepository->createWallet($userId);

        // 6. Crear quizz diario y puntos de aula
        $this->registrationRepository->createDailyQuizz($userId);
        $this->registrationRepository->createClassroomPoints($userId);

        // 7. Guardar expiración de membresía
        $this->registrationRepository->saveMembershipExpiration($userId, $user->idAccountType);

        // 8. Crear pago (si no es free tier)
        if (!$user->isFreeTier() && !empty($paymentData)) {
            $paymentData['user_id'] = $userId;
            $this->registrationRepository->createPayment($paymentData);
        }

        // 9. Clasificación binaria
        $referrer = $this->registrationRepository->findReferrer($user->idReferrerSponsor);

        if ($referrer) {
            $referrerPosition = $referrer['position'] ?? 0;
            
            // Priorizar la posición guardada en el preregistro/registro sobre el ajuste actual del sponsor
            $chosenPosition = ($user->position !== null) ? $user->position : $referrerPosition;
            
            $position = $chosenPosition == 0 ? 'user_position_left' : 'user_position_right';
            $userAbove = $this->registrationRepository->getLastUserBeforeEmpty($user->idReferrerSponsor, $position);

            $this->registrationRepository->createClassified([
                'user_id'        => $userId,
                'id_user_sponsor' => $user->idReferrerSponsor,
                'binary_sponsor' => $referrer['username'] ?? 'unknown',
                'position'       => $chosenPosition,
                'classification' => 16,
                'status'         => '0',
                'authorized'     => $user->isFreeTier() ? '0' : '1',
                'user_above'     => $userAbove,
            ]);
        }

        // 9.b Puntos binarios y bono de inicio rápido.
        // Solo si el alta queda aprobada (pago confirmado o cuenta libre de verificación).
        // Si queda pendiente, los reparte la aprobación de la solicitud, para no pagar
        // dos veces por el mismo afiliado.
        if ($user->getRequestStatus() === 2) {
            $this->registrationRepository->distributeAffiliationRewards($userId);
        }

        // 10. Notificación al patrocinador
        $this->registrationRepository->createNotification([
            'id_generator' => $userId,
            'id_receiver'  => $user->idReferrerSponsor,
            'title'        => 'Registro de Nuevo Afiliado',
            'body'         => $user->name . ' ' . $user->lastName . ' se acaba de registrar con tu enlace',
            'type'         => 1,
        ]);

        if ($user->getRequestStatus() === 1) {
            $this->registrationRepository->createNotification([
                'id_generator' => $userId,
                'id_receiver'  => 1, // Admin (id=1)
                'title'        => 'Nuevo Usuario Pendiente',
                'body'         => $user->name . ' ' . $user->lastName . ' (' . $user->username . ') se ha registrado y está pendiente de verificación.',
                'type'         => 1, // Assuming 1 is standard notification type
            ]);
        }

        // 11. Envío de correo de bienvenida (no bloquea si falla)
        try {
            $this->notificationService->sendWelcomeEmail(
                $user->email,
                $user->username,
                $rawPassword
            );
        } catch (Exception $e) {
            // Se loguea internamente pero no se propaga
        }

        return [
            'user_id'  => $userId,
            'username' => $user->username,
            'is_free'  => $user->isFreeTier(),
        ];
    }
}
