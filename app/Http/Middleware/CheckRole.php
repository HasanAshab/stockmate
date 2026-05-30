<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check() || strtolower(auth()->user()->role->name) !== strtolower($role)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
