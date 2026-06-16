<?php

namespace App\Http\Middleware;

use App\Exceptions\Auth\ForbiddenException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = Auth::user();
        } catch (\Throwable $e) {
            //throw $th;
            throw new ForbiddenException(null, 0, $e);
        }
        return $next($request);
    }
}
