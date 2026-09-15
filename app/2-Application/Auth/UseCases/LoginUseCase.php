<?php
namespace Promolider\Application\Auth\UseCases;

use Promolider\Domain\Auth\Ports\Out\UserRepositoryInterface;
use Promolider\Domain\Auth\Ports\Out\PasswordHasherInterface;
use Promolider\Domain\Auth\Ports\Out\TokenGeneratorInterface;
use Promolider\Domain\Auth\Services\LoginThrottle;
use Promolider\Domain\Auth\ValueObjects\LoginAttemptKey;
use Exception;

class LoginUseCase
{
    /**
     * Hash bcrypt valido que no corresponde a ninguna contrasena en uso.
     *
     * Cuando el usuario no existe se verifica igualmente contra este hash para que la
     * respuesta tarde lo mismo que con un usuario real y contrasena incorrecta. Sin esto,
     * el "no existe" contesta notablemente antes que el "existe pero la clave falla", y ese
     * desfase basta para enumerar cuentas aunque el cuerpo de la respuesta sea identico.
     */
    private const DUMMY_HASH = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator,
        private LoginThrottle $loginThrottle
    ) {}

    public function execute(string $username, string $password, string $ip): array
    {
        $throttleKey = LoginAttemptKey::fromIdentifierAndIp($username, $ip);

        // 0. Si el origen esta bloqueado, cortar antes de tocar la base de datos.
        $this->loginThrottle->assertNotLocked($throttleKey);

        // 1. Obtener usuario del repositorio (No nos importa si es DB, API, etc)
        $user = $this->userRepository->findByUsername($username);

        // 2. Verificar existencia y contrasena.
        //    Mismo camino, mismo coste y misma respuesta tanto si el usuario no existe como
        //    si la contrasena es incorrecta: nada distingue un caso del otro hacia fuera.
        $credentialsAreValid = $user
            ? $this->passwordHasher->verify($password, $user->password)
            : $this->passwordHasher->verify($password, self::DUMMY_HASH);

        if (!$credentialsAreValid) {
            // Al agotar el limite esto lanza TooManyLoginAttemptsException (429) en lugar del 401.
            $this->loginThrottle->registerFailure($throttleKey);

            throw new Exception("Unauthorized", 401);
        }

        // 3. Validar regla de negocio (status request == 2).
        //    Las credenciales eran correctas, asi que no cuenta como intento fallido.
        if (!$user->isAllowedToLogin()) {
            throw new Exception("Forbidden", 403);
        }

        // 4. Login correcto: el origen deja de estar penalizado.
        $this->loginThrottle->clear($throttleKey);

        // 5. Generar Token
        $token = $this->tokenGenerator->generateTokenForUser($user);

        // 6. Devolver datos estructurados
        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'photo' => $user->photo,
            ],
            'role' => $user->roles,
        ];
    }
}
