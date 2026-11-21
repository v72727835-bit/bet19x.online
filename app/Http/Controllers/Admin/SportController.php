<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Sport;
use Illuminate\Http\Request;

class SportController extends Controller
{
    public function index()
    {
        $sports = Sport::withCount('matchEvents')->orderBy('name')->get();

        return view('admin.sports.index', compact('sports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sports,slug',
            'icon' => 'nullable|string|max:255',
        ]);

        $data['is_active'] = true;
        Sport::create($data);

        AuditLog::record('Sports', 'Create Sport', "Created sport: {$data['name']}");

        return back()->with('status', 'Sport add ho gaya.');
    }

    public function toggle(Sport $sport)
    {
        $sport->update(['is_active' => ! $sport->is_active]);

        return back()->with('status', 'Sport status update ho gaya.');
    }

    public function destroy(Sport $sport)
    {
        $sport->delete();

        AuditLog::record('Sports', 'Delete Sport', "Deleted sport #{$sport->id}");

        return back()->with('status', 'Sport delete ho gaya.');
    }
}
