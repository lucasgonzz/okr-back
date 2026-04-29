<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Allow the request only if the authenticated user has one of the given roles.
     * Usage in routes: ->middleware('role:super_admin')  or  'role:admin,user'.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden: role not allowed.'], 403);
        }

        return $next($request);
    }
}
