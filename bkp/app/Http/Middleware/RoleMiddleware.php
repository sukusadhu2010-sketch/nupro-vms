<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use App\Models\Role;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $user = Auth::user();

        if (! $user->hasRole($role)) {
            abort(403, 'Access denied. You do not have the required role.');
        }

        return $next($request);
    }
}

