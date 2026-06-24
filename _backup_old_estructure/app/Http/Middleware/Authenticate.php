<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('main-login');
        }
    }

    /**
     * Handle an unauthenticated user. This is a copy of the original method by Diego
     */

    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }
        // Exclude specific route from authentication

        $exclude = ['mc.filter', 'mc.upcoming'];

        if (in_array($request->route()->getName(), $exclude)) {
            return $this->auth->shouldUse($guard);
        }
        $this->unauthenticated($request, $guards);
    }
}
