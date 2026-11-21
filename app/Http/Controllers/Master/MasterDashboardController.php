<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSetting;
use App\Models\Tenant;

class MasterDashboardController extends Controller
{
    public function index()
    {
        $masterSetting = MasterSetting::first();

        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $provisioningTenants = Tenant::where('status', 'provisioning')->count();
        $failedTenants = Tenant::where('status', 'failed')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        $subscriptionTenants = Tenant::where('sale_type', 'subscription');
        $oneTimeTenants = Tenant::where('sale_type', 'one_time');

        $totalOneTimeRevenue = (clone $oneTimeTenants)->sum('price');
        $monthlyRecurringEstimate = (clone $subscriptionTenants)->get()->sum(function ($t) {
            return match ($t->billing_cycle) {
                'monthly' => (float) $t->price,
                'quarterly' => (float) $t->price / 3,
                'half_yearly' => (float) $t->price / 6,
                'yearly' => (float) $t->price / 12,
                default => 0,
            };
        });

        $overdueTenants = Tenant::where('sale_type', 'subscription')
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<', now())
            ->get();

        $recentTenants = Tenant::latest()->take(5)->get();

        return view('master.dashboard', compact(
            'masterSetting', 'totalTenants', 'activeTenants', 'provisioningTenants', 'failedTenants',
            'suspendedTenants', 'totalOneTimeRevenue', 'monthlyRecurringEstimate', 'overdueTenants', 'recentTenants'
        ));
    }
}
