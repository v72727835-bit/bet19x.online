<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedContest;
use Illuminate\Http\Request;

class FeaturedContestController extends Controller
{
    public function index()
    {
        $contests = FeaturedContest::orderBy('sort_order')->get();

        return view('admin.featured-contests', compact('contests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:150',
            'prize_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'image_url' => 'nullable|url|max:500',
        ]);

        if (!$request->hasFile('image') && !$request->filled('image_url')) {
            return back()->withErrors(['image' => 'Ya to image upload karein, ya external image URL daalein.']);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('contests', 'public');
        } else {
            $path = $request->image_url;
        }

        FeaturedContest::create([
            'image_path' => $path,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'prize_text' => $request->prize_text,
            'link_url' => $request->link_url,
            'sort_order' => (int) FeaturedContest::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Contest add ho gaya.');
    }

    public function toggle(FeaturedContest $featuredContest)
    {
        $featuredContest->update(['is_active' => !$featuredContest->is_active]);

        return back()->with('status', 'Contest status update ho gaya.');
    }

    public function destroy(FeaturedContest $featuredContest)
    {
        if (!str_starts_with($featuredContest->image_path, 'http')) {
            \Storage::disk('public')->delete($featuredContest->image_path);
        }

        $featuredContest->delete();

        return back()->with('status', 'Contest delete ho gaya.');
    }
}
