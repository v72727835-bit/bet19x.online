<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Requests to these hosts always use the landlord/default database and skip
     * tenant resolution entirely — this is where the Master Panel itself lives.
     * Add your control-panel domain here (or leave as-is and manage tenants via
     * whatever domain currently points at DB_DATABASE in .env).
     */
    protected array $masterHosts = [];

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower(preg_replace('/^www\./', '', $request->getHost()));

        // /master routes always run on the landlord DB, regardless of domain,
        // so the reseller can manage tenants from any host that reaches this app.
        if ($request->is('master') || $request->is('master/*')) {
            return $next($request);
        }

        $tenant = Tenant::where('domain', $host)->where('status', 'active')->first();

        if (! $tenant) {
            // Unknown domain — fall through to whatever DB_DATABASE is configured
            // in .env (keeps single-tenant / pre-white-label installs working).
            return $next($request);
        }

        if (! $tenant->isLicenseValid()) {
            return response()->view('errors.license-expired', [
                'supportEmail' => null,
                'supportPhone' => null,
            ], 403);
        }

        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.port', $tenant->db_port);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_username);
        Config::set('database.connections.tenant.password', $tenant->db_password);

        // Drop any previously-resolved 'tenant' PDO connection from a prior request
        // sharing this worker/process, so the new config actually takes effect.
        DB::purge('tenant');

        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
