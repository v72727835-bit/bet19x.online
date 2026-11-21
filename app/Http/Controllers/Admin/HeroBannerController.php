<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use Illuminate\Http\Request;

class HeroBannerController extends Controller
{
    public function index()
    {
        $banners = HeroBanner::orderBy('sort_order')->get();

        return view('admin.hero-banners', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'image_url' => 'nullable|url|max:500',
        ]);

        if (!$request->hasFile('image') && !$request->filled('image_url')) {
            return back()->withErrors(['image' => 'Ya to image upload karein, ya external image URL daalein.']);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
        } else {
            $path = $request->image_url;
        }

        HeroBanner::create([
            'image_path' => $path,
            'title' => $request->title,
            'link_url' => $request->link_url,
            'sort_order' => (int) HeroBanner::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Banner add ho gaya.');
    }

    public function toggle(HeroBanner $heroBanner)
    {
        $heroBanner->update(['is_active' => !$heroBanner->is_active]);

        return back()->with('status', 'Banner status update ho gaya.');
    }

    public function destroy(HeroBanner $heroBanner)
    {
        if (!str_starts_with($heroBanner->image_path, 'http')) {
            \Storage::disk('public')->delete($heroBanner->image_path);
        }

        $heroBanner->delete();

        return back()->with('status', 'Banner delete ho gaya.');
    }
}
