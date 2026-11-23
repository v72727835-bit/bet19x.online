<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AsTechController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use App\Services\AsTechApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/** Choose which game providers appear first on the home page. */
class ProviderOrderController extends Controller
{
    public function index()
    {
        $meta = app(AsTechController::class)->gameMeta(app(AsTechApi::class))->getData(true);
        $providers = [];
        foreach ($meta['provider_order'] ?? array_keys($meta['providers'] ?? []) as $k) {
            $providers[$k] = [
                'count' => $meta['providers'][$k] ?? 0,
                'name' => $meta['provider_info'][$k]['name'] ?? $k,
                'logo' => $meta['provider_info'][$k]['logo'] ?? null,
            ];
        }
        $order = SiteSetting::providerOrder();
        $ready = $this->ready();

        return view('admin.providers.index', compact('providers', 'order', 'ready'));
    }

    public function update(Request $request)
    {
        if (! $this->ready()) {
            return back()->withErrors(['order' => 'Pehle server par migration chalaiye (php artisan migrate), phir order save hoga.']);
        }

        $data = $request->validate([
            'position' => 'array',
            'position.*' => 'nullable|integer|min:1|max:999',
        ]);
        $order = collect($data['position'] ?? [])->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (int) $v)->sort()->all();

        $setting = SiteSetting::first() ?? new SiteSetting(['site_name' => 'BET19X']);
        $setting->provider_order = $request->boolean('clear') ? null : $order;
        $setting->save();

        AuditLog::record('Content', 'Provider Order', $request->boolean('clear') ? 'Cleared pinned providers' : 'Pinned: ' . json_encode($order));

        return back()->with('status', $request->boolean('clear') ? 'Order hata diya, providers ab games ki ginti ke hisaab se dikhenge.' : count($order) . ' providers aage pin ho gaye.');
    }

    private function ready(): bool
    {
        return Schema::connection((new SiteSetting)->getConnectionName())->hasColumn('site_settings', 'provider_order');
    }
}
