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
        if (! $user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }
        $userRole = $user->role ?? 'customer';
        if (! in_array($userRole, $roles, true)) {
            return response()->json(['error' => 'forbidden', 'required_roles' => $roles], 403);
        }
        return $next($request);
    }
}
