<?php
namespace Promolider\Infrastructure\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Auth\Ports\Out\UserRepositoryInterface;
use Promolider\Domain\Auth\Ports\Out\PasswordHasherInterface;
use Promolider\Domain\Auth\Ports\Out\TokenGeneratorInterface;
use Promolider\Domain\Auth\Ports\Out\LoginThrottleStoreInterface;
use Promolider\Domain\Auth\Services\LoginThrottle;
use Promolider\Infrastructure\Auth\Out\Persistence\EloquentUserRepository;
use Promolider\Infrastructure\Auth\Out\Security\LaravelPasswordHasher;
use Promolider\Infrastructure\Auth\Out\Security\SanctumTokenGenerator;
use Promolider\Infrastructure\Auth\Out\Security\CacheLoginThrottleStore;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind Ports (Domain) to Adapters (Infrastructure)
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenGeneratorInterface::class, SanctumTokenGenerator::class);
        $this->app->bind(LoginThrottleStoreInterface::class, CacheLoginThrottleStore::class);

        // Por defecto: 5 fallos bloquean. 
        // El 1er bloqueo dura 1 min, el 2do 2 min, el 3ero 4 min
        // hasta un techo de 1 hora. El nivel alcanzado se recuerda 24 h desde el
        // último fallo, que es lo que hace que el backoff sea progresivo de verdad y no se
        // reinicie en cuanto expira un bloqueo.
        $this->app->bind(LoginThrottle::class, function ($app) {
            $config = $app['config']->get('auth.login_throttle', []);

            return new LoginThrottle(
                $app->make(LoginThrottleStoreInterface::class),
                (int) ($config['max_attempts']     ?? 5),
                (int) ($config['lock_seconds']     ?? 60),
                (int) ($config['lock_multiplier']  ?? 2),
                (int) ($config['max_lock_seconds'] ?? 3600),
                (int) ($config['memory_seconds']   ?? 86400)
            );
        });
    }

    public function boot()
    {
        // 
    }
}
