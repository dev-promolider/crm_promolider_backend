<?php
namespace Promolider\Domain\Auth\Entities;

class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $password,
        public bool $isApproved,
        public array $roles = [],
        public string $name = '',
        public string $last_name = '',
        public ?string $photo = null
    ) {}

    /**
     * Regla de negocio: El usuario solo puede hacer login si está aprobado (is_approved = true).
     */
    public function isAllowedToLogin(): bool
    {
        return $this->isApproved;
    }
}
