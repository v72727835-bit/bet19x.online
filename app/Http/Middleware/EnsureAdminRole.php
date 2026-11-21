<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Restrict a route to specific admin roles.
     * Usage: ->middleware('admin.role:super_admin,manager')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $admin = auth('admin')->user();

        if (! $admin || (! empty($roles) && ! in_array($admin->role, $roles))) {
            abort(403, 'Aapke role ko is section ki permission nahi hai.');
        }

        if (! $admin->is_active) {
            auth('admin')->logout();
            abort(403, 'Aapka admin account deactivate kar diya gaya hai.');
        }

        return $next($request);
    }
}
