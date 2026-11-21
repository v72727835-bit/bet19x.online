<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SiteSetting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use PDOException;
use Throwable;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->paginate(20);
        $masterSetting = \App\Models\MasterSetting::first();

        return view('master.tenants.index', compact('tenants', 'masterSetting'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'domain' => 'required|string|max:150|unique:tenants,domain',
            'brand_name' => 'required|string|max:100',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:150',
            'sale_type' => 'required|in:one_time,subscription',
            'price' => 'nullable|numeric|min:0',
            'billing_cycle' => 'nullable|in:monthly,quarterly,half_yearly,yearly',
            'next_billing_date' => 'nullable|date',
            'client_notes' => 'nullable|string|max:1000',
            'db_name' => 'required|string|max:64',
            'db_username' => 'required|string|max:64',
            'db_password' => 'nullable|string|max:100',
            'db_host' => 'nullable|string|max:100',
            'db_port' => 'nullable|string|max:10',
            'admin_email' => 'required|email|max:150',
            'admin_password' => 'required|string|min:6',
            'license_expires_at' => 'nullable|date',
        ]);

        $data['domain'] = strtolower(preg_replace('/^www\./', '', trim($data['domain'])));
        $data['db_host'] = $data['db_host'] ?: '127.0.0.1';
        $data['db_port'] = $data['db_port'] ?: '3306';
        $data['status'] = 'provisioning';

        $tenant = Tenant::create(collect($data)->except(['admin_email', 'admin_password'])->toArray());

        $this->provision($tenant, $data['admin_email'], $data['admin_password']);

        return redirect()->route('master.tenants.index')->with('status', "Tenant '{$tenant->brand_name}' provisioning {$tenant->fresh()->status}. Neeche log dekho.");
    }

    public function retry(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'admin_email' => 'required|email|max:150',
            'admin_password' => 'required|string|min:6',
        ]);

        $this->provision($tenant, $data['admin_email'], $data['admin_password']);

        return back()->with('status', "Tenant '{$tenant->brand_name}' provisioning dobara try ho gaya — status: {$tenant->fresh()->status}.");
    }

    public function toggle(Tenant $tenant)
    {
        $tenant->update(['status' => $tenant->status === 'active' ? 'suspended' : 'active']);

        return back()->with('status', 'Tenant status update ho gaya.');
    }

    public function destroy(Tenant $tenant)
    {
        // We deliberately do NOT drop the tenant's database here — that would
        // permanently delete a client's live data. This only removes the
        // domain-to-database mapping; the DB itself must be dropped manually
        // in cPanel if you really want to delete a client's data.
        $tenant->delete();

        return back()->with('status', 'Tenant mapping hata diya gaya (database delete nahi hua — wo cPanel se manually karo agar chahiye).');
    }

    /**
     * Attempts to create the DB (best-effort — most shared cPanel accounts won't allow
     * this and it will silently fail, in which case the DB must already exist), then
     * runs the full migration set against it and seeds the tenant's first admin login.
     */
    protected function provision(Tenant $tenant, string $adminEmail, string $adminPassword): void
    {
        $log = [];

        try {
            $this->attemptCreateDatabase($tenant, $log);

            Config::set('database.connections.tenant.host', $tenant->db_host);
            Config::set('database.connections.tenant.port', $tenant->db_port);
            Config::set('database.connections.tenant.database', $tenant->db_name);
            Config::set('database.connections.tenant.username', $tenant->db_username);
            Config::set('database.connections.tenant.password', $tenant->db_password);
            DB::purge('tenant');

            // Sanity check the connection works before migrating.
            DB::connection('tenant')->getPdo();
            $log[] = 'Tenant DB se connection successful.';

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $log[] = 'Migrations run ho gaye: '.trim(Artisan::output());

            if (! SiteSetting::on('tenant')->exists()) {
                SiteSetting::on('tenant')->create([
                    'site_name' => $tenant->brand_name,
                    'ticker_text' => "Welcome to {$tenant->brand_name}!",
                ]);
                $log[] = 'Default branding seed ho gayi.';
            }

            if (! Admin::on('tenant')->where('email', $adminEmail)->exists()) {
                Admin::on('tenant')->create([
                    'name' => $tenant->brand_name.' Admin',
                    'email' => $adminEmail,
                    'password' => bcrypt($adminPassword),
                    'role' => 'super_admin',
                    'is_active' => true,
                ]);
                $log[] = "Admin login ready: {$adminEmail}";
            }

            $tenant->update(['status' => 'active', 'provision_log' => implode("\n", $log)]);
        } catch (Throwable $e) {
            $log[] = 'ERROR: '.$e->getMessage();
            $tenant->update(['status' => 'failed', 'provision_log' => implode("\n", $log)]);
        }
    }

    protected function attemptCreateDatabase(Tenant $tenant, array &$log): void
    {
        try {
            $pdo = new PDO(
                "mysql:host={$tenant->db_host};port={$tenant->db_port}",
                $tenant->db_username,
                $tenant->db_password ?? ''
            );
            $dbNameSafe = preg_replace('/[^A-Za-z0-9_]/', '', $tenant->db_name);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbNameSafe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $log[] = "Database `{$dbNameSafe}` create/verify ho gaya.";
        } catch (PDOException $e) {
            // Very common on shared cPanel hosting: the DB user can't CREATE DATABASE.
            // In that case the database must already exist (created via cPanel MySQL
            // Wizard) with the given username already granted access to it.
            $log[] = 'Note: Database auto-create nahi ho paya ('.$e->getMessage().'). Agar database cPanel se pehle se bana hua hai to yeh normal hai, aage badh rahe hain.';
        }
    }
}
