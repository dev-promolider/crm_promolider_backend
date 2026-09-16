<?php
namespace Promolider\Infrastructure\Auth\Out\Security;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Promolider\Domain\Auth\Ports\Out\LoginThrottleStoreInterface;
use Promolider\Domain\Auth\ValueObjects\LoginThrottleState;

/**
 * Adaptador del puerto LoginThrottleStoreInterface sobre el cache de Laravel.
 *
 * Se inyecta el contrato Repository, no la facade, asi que usa el store por defecto
 * configurado en config/cache.php (hoy 'file'): cambiar a redis en el .env no toca codigo.
 *
 * Nota operativa: con driver 'file' el contador es por instancia del backend. Si en algun
 * momento se sirve la API desde varios nodos, hay que pasar CACHE_DRIVER a redis para que
 * el limite sea realmente global.
 */
class CacheLoginThrottleStore implements LoginThrottleStoreInterface
{
    private const PREFIX = 'auth:login-throttle:';

    public function __construct(
        private CacheRepository $cache
    ) {}

    public function get(string $key): ?LoginThrottleState
    {
        $raw = $this->cache->get($this->cacheKey($key));

        if (!is_array($raw)) {
            return null;
        }

        return LoginThrottleState::fromArray($raw);
    }

    public function put(string $key, LoginThrottleState $state, int $ttlSeconds): void
    {
        $this->cache->put($this->cacheKey($key), $state->toArray(), max(1, $ttlSeconds));
    }

    public function forget(string $key): void
    {
        $this->cache->forget($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return self::PREFIX . $key;
    }
}
