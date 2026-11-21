<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $setting = SiteSetting::first() ?? SiteSetting::create(['site_name' => 'FantasyXI']);

        return view('admin.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:50',
            'app_tagline' => 'nullable|string|max:100',
            'ticker_text' => 'nullable|string|max:150',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,ico|max:512',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'bg_dark_color' => 'nullable|string|max:20',
            'surface_dark_color' => 'nullable|string|max:20',
            'currency_symbol' => 'nullable|string|max:5',
            'support_email' => 'nullable|email|max:100',
            'support_phone' => 'nullable|string|max:20',
            'footer_text' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_twitter' => 'nullable|string|max:255',
            'social_telegram' => 'nullable|string|max:255',
        ]);

        $setting = SiteSetting::first() ?? new SiteSetting();

        $setting->fill($request->only([
            'site_name', 'app_tagline', 'ticker_text',
            'primary_color', 'secondary_color', 'accent_color', 'bg_dark_color', 'surface_dark_color',
            'currency_symbol', 'support_email', 'support_phone', 'footer_text',
            'social_facebook', 'social_instagram', 'social_twitter', 'social_telegram',
        ]));

        if ($request->hasFile('logo')) {
            $setting->logo_path = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('favicon')) {
            $setting->favicon_path = $request->file('favicon')->store('logos', 'public');
        }

        $setting->save();

        AuditLog::record('Settings', 'Update Branding', 'Site branding / white-label settings updated');

        return back()->with('status', 'Branding settings update ho gayi.');
    }

    /**
     * License fields — restricted to super_admin so a reselling agency
     * can hand over the panel without exposing license controls.
     */
    public function updateLicense(Request $request)
    {
        if (! (auth('admin')->user()?->isSuperAdmin())) {
            abort(403, 'Sirf super admin license update kar sakta hai.');
        }

        $data = $request->validate([
            'license_key' => 'nullable|string|max:100',
            'licensed_to' => 'nullable|string|max:150',
            'licensed_domain' => 'nullable|string|max:150',
            'license_expires_at' => 'nullable|date',
        ]);

        $setting = SiteSetting::first() ?? new SiteSetting();
        $setting->fill($data);
        $setting->save();

        AuditLog::record('Settings', 'Update License', "License updated for: {$setting->licensed_to}");

        return back()->with('status', 'License details update ho gaye.');
    }
}
