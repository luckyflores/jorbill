<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isTech')) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }
        // Allow if user.role matches any of the listed roles
        if (! in_array($user->role ?? null, $roles, true)) {
            return response()->json(['error' => 'forbidden', 'required_roles' => $roles], 403);
        }
        return $next($request);
    }
}
