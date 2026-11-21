<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteStat;
use Illuminate\Http\Request;

class SiteStatController extends Controller
{
    public function index()
    {
        $stats = SiteStat::orderBy('sort_order')->get();

        return view('admin.site-stats', compact('stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'icon' => 'nullable|string|max:10',
            'value' => 'required|string|max:30',
            'label' => 'required|string|max:60',
        ]);

        SiteStat::create([
            'icon' => $request->icon,
            'value' => $request->value,
            'label' => $request->label,
            'sort_order' => (int) SiteStat::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Stat add ho gaya.');
    }

    public function toggle(SiteStat $siteStat)
    {
        $siteStat->update(['is_active' => !$siteStat->is_active]);

        return back()->with('status', 'Stat status update ho gaya.');
    }

    public function destroy(SiteStat $siteStat)
    {
        $siteStat->delete();

        return back()->with('status', 'Stat delete ho gaya.');
    }
}
