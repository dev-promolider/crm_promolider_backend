<?php
namespace Promolider\Infrastructure\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Auth\Ports\Out\UserRepositoryInterface;
use Promolider\Domain\Auth\Ports\Out\PasswordHasherInterface;
use Promolider\Domain\Auth\Ports\Out\TokenGeneratorInterface;
use Promolider\Infrastructure\Auth\Out\Persistence\EloquentUserRepository;
use Promolider\Infrastructure\Auth\Out\Security\LaravelPasswordHasher;
use Promolider\Infrastructure\Auth\Out\Security\SanctumTokenGenerator;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind Ports (Domain) to Adapters (Infrastructure)
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenGeneratorInterface::class, SanctumTokenGenerator::class);
    }

    public function boot()
    {
        // 
    }
}
