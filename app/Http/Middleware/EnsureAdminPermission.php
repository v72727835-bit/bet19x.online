<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * Usage: ->middleware('admin.perm:wallet')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $admin = auth('admin')->user();

        if (! $admin || ! $admin->hasPermission($module)) {
            abort(403, 'Aapko is section ki permission nahi hai.');
        }

        return $next($request);
    }
}
