<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $agent = Auth::guard('agent')->user();
        if ($agent && ! $agent->is_active && ! $request->session()->has('agent_impersonator_admin_id')) {
            Auth::guard('agent')->logout();

            return redirect()->route('agent.login')->withErrors(['email' => 'Aapka agent account deactivate hai.']);
        }

        return $next($request);
    }
}
