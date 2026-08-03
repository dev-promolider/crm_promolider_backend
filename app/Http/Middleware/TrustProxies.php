<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Configure via TRUSTED_PROXIES env (comma-separated IPs or CIDR).
     * '*' trusts none in a safe way by default; production must set explicit IPs.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $configured = env('TRUSTED_PROXIES');
        if (is_string($configured) && $configured !== '') {
            $this->proxies = array_map('trim', explode(',', $configured));
        } else {
            $this->proxies = null;
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
