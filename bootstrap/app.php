<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\CheckBlockedIp;
use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'admin.role' => EnsureAdminRole::class,
            'admin.perm' => \App\Http\Middleware\EnsureAdminPermission::class,
        ]);

        // ResolveTenant MUST run before anything else that queries a
        // tenant-scoped model (BlockedIp, SiteSetting, Admin, etc.).
        $middleware->web(prepend: [
            ResolveTenant::class,
        ]);

        $middleware->web(append: [
            CheckBlockedIp::class,
            CheckLicense::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('master') || $request->is('master/*')) {
                return route('master.login');
            }

            return $request->is('admin/*') ? route('admin.login') : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
