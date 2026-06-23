<?php
namespace Promolider\Domain\Auth\Ports\Out;

use Promolider\Domain\Auth\Entities\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;
}
