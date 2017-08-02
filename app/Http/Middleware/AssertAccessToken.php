<?php

namespace App\Http\Middleware;

use Closure;

class AssertAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($user = $request->user()) {
            $user->assertValidAccessToken();
        }

        return $next($request);
    }
}