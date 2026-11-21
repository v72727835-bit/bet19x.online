<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('master') || $request->is('master/*')) {
            return $next($request);
        }

        if (BlockedIp::where('ip_address', $request->ip())->exists()) {
            abort(403, 'Aapka IP address block kar diya gaya hai.');
        }

        return $next($request);
    }
}
