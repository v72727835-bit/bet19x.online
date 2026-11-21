<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopWinner;
use Illuminate\Http\Request;

class TopWinnerController extends Controller
{
    public function index()
    {
        $winners = TopWinner::orderBy('sort_order')->get();

        return view('admin.top-winners', compact('winners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'contest_name' => 'required|string|max:150',
            'amount_won' => 'required|numeric|min:0',
            'avatar_url' => 'nullable|url|max:500',
        ]);

        TopWinner::create([
            'name' => $request->name,
            'contest_name' => $request->contest_name,
            'amount_won' => $request->amount_won,
            'avatar_url' => $request->avatar_url,
            'sort_order' => (int) TopWinner::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Winner add ho gaya.');
    }

    public function toggle(TopWinner $topWinner)
    {
        $topWinner->update(['is_active' => !$topWinner->is_active]);

        return back()->with('status', 'Winner status update ho gaya.');
    }

    public function destroy(TopWinner $topWinner)
    {
        $topWinner->delete();

        return back()->with('status', 'Winner delete ho gaya.');
    }
}
