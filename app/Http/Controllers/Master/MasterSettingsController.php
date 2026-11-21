<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSetting;
use Illuminate\Http\Request;

class MasterSettingsController extends Controller
{
    public function edit()
    {
        $setting = MasterSetting::first() ?? MasterSetting::create([]);

        return view('master.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'primary_color' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $setting = MasterSetting::first() ?? new MasterSetting();
        $setting->company_name = $request->company_name;
        $setting->primary_color = $request->primary_color ?: $setting->primary_color;

        if ($request->hasFile('logo')) {
            $setting->logo_path = $request->file('logo')->store('master-logos', 'public');
        }

        $setting->save();

        return back()->with('status', 'Master Panel branding update ho gayi.');
    }
}
