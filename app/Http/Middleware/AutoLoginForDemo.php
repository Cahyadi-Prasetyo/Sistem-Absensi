<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginForDemo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auto login first user for demo purposes
        if (!auth()->check() && config('app.env') === 'local') {
            $user = \App\Models\User::first();
            if ($user) {
                auth()->login($user);
            }
        }

        return $next($request);
    }
}
