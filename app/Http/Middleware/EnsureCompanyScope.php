<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyScope
{
    /**
     * Ensure the authenticated user belongs to a company. All company-scoped routes
     * rely on $request->user()->company_id to filter queries.
     * A super_admin has no company and must not reach these routes, so we reject here.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (empty($user->company_id)) {
            return response()->json([
                'message' => 'Forbidden: user is not assigned to a company.',
            ], 403);
        }

        return $next($request);
    }
}
