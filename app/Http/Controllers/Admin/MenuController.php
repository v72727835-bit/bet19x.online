<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::orderBy('sort_order')->get();

        return view('admin.menu', compact('menuItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:30',
            'url' => 'required|string|max:150',
        ]);

        MenuItem::create([
            'label' => $request->label,
            'url' => $request->url,
            'sort_order' => MenuItem::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Menu item added.');
    }

    public function toggle(MenuItem $menuItem)
    {
        $menuItem->update(['is_active' => ! $menuItem->is_active]);

        return back()->with('status', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return back()->with('status', 'Menu item deleted.');
    }
}
