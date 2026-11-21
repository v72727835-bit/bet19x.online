<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SportCategory;
use Illuminate\Http\Request;

class SportCategoryController extends Controller
{
    public function index()
    {
        $categories = SportCategory::orderBy('sort_order')->get();

        return view('admin.sport-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:10',
            'link_url' => 'nullable|string|max:255',
        ]);

        SportCategory::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'link_url' => $request->link_url,
            'sort_order' => (int) SportCategory::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Category add ho gaya.');
    }

    public function toggle(SportCategory $sportCategory)
    {
        $sportCategory->update(['is_active' => !$sportCategory->is_active]);

        return back()->with('status', 'Category status update ho gaya.');
    }

    public function destroy(SportCategory $sportCategory)
    {
        $sportCategory->delete();

        return back()->with('status', 'Category delete ho gaya.');
    }
}
