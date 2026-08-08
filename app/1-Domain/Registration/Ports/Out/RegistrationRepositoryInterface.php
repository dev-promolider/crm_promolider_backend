<?php
namespace Promolider\Domain\Registration\Ports\Out;

use Promolider\Domain\Registration\Entities\RegistrationUser;

interface RegistrationRepositoryInterface
{
    /**
     * Crea el usuario en la base de datos y retorna su ID.
     */
    public function createUser(RegistrationUser $user): int;

    /**
     * Crea la billetera del usuario.
     */
    public function createWallet(int $userId): void;

    /**
     * Registra un pago asociado al usuario.
     */
    public function createPayment(array $paymentData): int;

    /**
     * Crea el registro de clasificación binaria.
     */
    public function createClassified(array $data): void;

    /**
     * Crea una notificación para el patrocinador.
     */
    public function createNotification(array $data): void;

    /**
     * Busca los datos del patrocinador (referrer) por ID.
     */
    public function findReferrer(int $id): ?array;
    
    public function updateUserPosition(int $userId, int $position): bool;

    /**
     * Elimina el enlace compartido del patrocinador (una vez usado).
     */
    public function deleteSharedLink(int $userId): void;

    /**
     * Obtiene la posición del último usuario en la rama binaria.
     */
    public function getLastUserBeforeEmpty(int $referrerId, string $position): ?int;

    /**
     * Asigna un rol al usuario.
     */
    public function assignRole(int $userId, string $role): void;

    /**
     * Obtiene el avatar por defecto del sistema.
     */
    public function getDefaultAvatar(): ?string;

    /**
     * Crea el registro de quizz diario.
     */
    public function createDailyQuizz(int $userId): void;

    /**
     * Crea el registro de puntos de aula.
     */
    public function createClassroomPoints(int $userId): void;

    /**
     * Guarda la fecha de expiración de membresía.
     */
    public function saveMembershipExpiration(int $userId, int $accountTypeId): void;

    /**
     * Busca los datos del patrocinador por nombre de usuario.
     */
    public function findSponsorByUsername(string $username): ?array;

    /**
     * Resuelve el tipo de cuenta por defecto o por configuración.
     */
    public function resolveAccountType(): array;

    /**
     * Resuelve el país por nombre.
     */
    public function resolveCountry(?string $countryName): array;

    /**
     * Resuelve el tipo de documento por nombre.
     */
    public function resolveDocumentType(string $documentType): array;

    /**
     * Valida un enlace de patrocinador.
     */
    public function validateSponsorLink(int $userId, string $code): ?array;

    /**
     * Obtiene los datos del formulario de registro (documentos, cuentas, paÃ­ses, mÃ©todos de pago).
     */
    public function getRegistrationFormData(): array;

    /**
     * Verifica la disponibilidad de un campo especÃ­fico (email, username, nro_document).
     */
    public function checkAvailability(string $field, string $value, ?int $documentType = null): bool;

    /**
     * Registra un participante para un minicurso.
     */
    public function registerMinicourseParticipant(int $userId): void;

    /**
     * Registra un participante para un ebook.
     */
    public function registerEbookParticipant(int $userId, int $ebookId = null): void;

    /**
     * Registra un participante para una masterclass.
     */
    public function registerMasterclassParticipant(int $userId): void;

    /**
     * Crea un nuevo enlace de patrocinador.
     */
    public function createSponsorLink(int $userId, string $url, \DateTime $start, \DateTime $end): array;

    /**
     * Obtiene el enlace de patrocinador activo para el usuario.
     */
    public function getActiveSponsorLink(int $userId): ?array;

    /**
     * Suspende (desactiva) el enlace de patrocinador activo.
     */
    public function suspendSponsorLink(int $linkId, int $userId): bool;

    /**
     * Elimina enlaces de patrocinador que ya expiraron para el usuario.
     */
    public function deleteExpiredSponsorLinks(int $userId): void;

    /**
     * Obtiene la lista de usuarios directos que completaron su registro (origen registro, pagado).
     */
    public function getRegisteredDirects(int $userId): array;
}
