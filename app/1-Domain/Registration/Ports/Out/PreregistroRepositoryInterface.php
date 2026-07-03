<?php
namespace Promolider\Domain\Registration\Ports\Out;

use Promolider\Domain\Registration\Entities\Preregistro;

interface PreregistroRepositoryInterface
{
    /**
     * Busca un preregistro por email.
     */
    public function findByEmail(string $email): ?Preregistro;

    /**
     * Busca un preregistro por token de acceso.
     */
    public function findByToken(string $token): ?Preregistro;

    /**
     * Crea un nuevo preregistro.
     */
    public function create(array $data): Preregistro;

    /**
     * Actualiza un preregistro existente (ej: regenerar token).
     */
    public function update(int $id, array $data): void;

    /**
     * Verifica duplicados en campos únicos (email, username, nro_document).
     */
    public function checkDuplicate(string $field, string $value): bool;

    /**
     * Verifica si un email ya existe como usuario registrado.
     */
    public function emailExistsAsUser(string $email): bool;

    /**
     * Busca un preregistro por ID.
     */
    public function findById(int $id): ?Preregistro;

    /**
     * Obtiene la configuración del enlace de preregistro (lado, tema, datos del referidor).
     */
    public function getPreregistroConfig(string $username): array;

    /**
     * Obtiene el ID del usuario no verificado pendiente.
     */
    public function getPendingUnverifiedId(string $email, ?int $preregistroId): ?int;
}
