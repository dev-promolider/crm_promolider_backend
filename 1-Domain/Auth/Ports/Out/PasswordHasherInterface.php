<?php
namespace Promolider\Domain\Auth\Ports\Out;

interface PasswordHasherInterface
{
    public function verify(string $plainText, string $hashed): bool;
}
