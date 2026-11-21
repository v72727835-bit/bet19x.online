<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use App\Models\VipTier;
use Illuminate\Http\Request;

class VipController extends Controller
{
    public function index()
    {
        $tiers = VipTier::withCount('users')->orderBy('sort_order')->orderBy('min_points')->get();
        $setting = SiteSetting::first() ?? SiteSetting::create(['site_name' => 'FantasyXI']);

        return view('admin.vip.index', compact('tiers', 'setting'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'min_points' => 'required|integer|min:0',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'deposit_bonus_percent' => 'required|numeric|min:0|max:100',
            'badge_color' => 'required|string|max:20',
            'perks' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $tier = VipTier::create($data);

        AuditLog::record('VIP & Loyalty', 'Create Tier', "Created VIP tier: {$tier->name}");

        return back()->with('status', 'VIP tier create ho gaya.');
    }

    public function update(Request $request, VipTier $vipTier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'min_points' => 'required|integer|min:0',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'deposit_bonus_percent' => 'required|numeric|min:0|max:100',
            'badge_color' => 'required|string|max:20',
            'perks' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $vipTier->update($data);

        AuditLog::record('VIP & Loyalty', 'Update Tier', "Updated VIP tier: {$vipTier->name}");

        return back()->with('status', 'VIP tier update ho gaya.');
    }

    public function toggle(VipTier $vipTier)
    {
        $vipTier->update(['is_active' => ! $vipTier->is_active]);

        return back()->with('status', 'Tier status update ho gaya.');
    }

    public function destroy(VipTier $vipTier)
    {
        AuditLog::record('VIP & Loyalty', 'Delete Tier', $vipTier->name);
        $vipTier->delete();

        return back()->with('status', 'VIP tier delete ho gaya.');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'loyalty_points_per_100_played' => 'required|numeric|min:0',
            'loyalty_point_value' => 'required|numeric|min:0',
        ]);

        $setting = SiteSetting::first() ?? new SiteSetting();
        $setting->fill($data);
        $setting->save();

        AuditLog::record('VIP & Loyalty', 'Update Loyalty Settings', 'Points ratio updated');

        return back()->with('status', 'Loyalty settings update ho gayi.');
    }
}
