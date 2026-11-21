<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never block the admin login/settings screens themselves, or the license won't be fixable.
        if ($request->routeIs('admin.login') || $request->routeIs('admin.logout') || $request->routeIs('admin.settings.*')
            || $request->is('master') || $request->is('master/*')) {
            return $next($request);
        }

        $setting = SiteSetting::first();

        if ($setting && ! $setting->isLicenseValid()) {
            return response()->view('errors.license-expired', [
                'supportEmail' => $setting->support_email,
                'supportPhone' => $setting->support_phone,
            ], 403);
        }

        return $next($request);
    }
}
