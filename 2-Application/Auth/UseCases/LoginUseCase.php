<?php
namespace Promolider\Application\Auth\UseCases;

use Promolider\Domain\Auth\Ports\Out\UserRepositoryInterface;
use Promolider\Domain\Auth\Ports\Out\PasswordHasherInterface;
use Promolider\Domain\Auth\Ports\Out\TokenGeneratorInterface;
use Exception;

class LoginUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator
    ) {}

    public function execute(string $username, string $password): array
    {
        // 1. Obtener usuario del repositorio (No nos importa si es DB, API, etc)
        $user = $this->userRepository->findByUsername($username);

        // 2. Verificar existencia y contraseña
        if (!$user || !$this->passwordHasher->verify($password, $user->password)) {
            throw new Exception("Unauthorized", 401);
        }

        // 3. Validar regla de negocio (status request == 2)
        if (!$user->isAllowedToLogin()) {
            throw new Exception("Forbidden", 403);
        }

        // 4. Generar Token
        $token = $this->tokenGenerator->generateTokenForUser($user);

        // 5. Devolver datos estructurados
        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id' => $user->id,
                'username' => $user->username,
            ],
            'role' => $user->roles,
        ];
    }
}
