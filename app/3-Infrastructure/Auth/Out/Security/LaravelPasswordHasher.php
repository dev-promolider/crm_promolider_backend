<?php
namespace Promolider\Infrastructure\Auth\Out\Security;

use Promolider\Domain\Auth\Ports\Out\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function verify(string $plainText, string $hashed): bool
    {
        return Hash::check($plainText, $hashed);
    }
}
