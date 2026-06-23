<?php
namespace Promolider\Domain\Auth\Entities;

class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $password,
        public int $requestStatus,
        public array $roles = []
    ) {}

    /**
     * Regla de negocio: El usuario solo puede hacer login si su status de solicitud (request) es 2.
     */
    public function isAllowedToLogin(): bool
    {
        return $this->requestStatus === 2;
    }
}
