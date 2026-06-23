<?php
namespace Promolider\Domain\Auth\Ports\Out;

use Promolider\Domain\Auth\Entities\User;

interface TokenGeneratorInterface
{
    public function generateTokenForUser(User $user): string;
}
