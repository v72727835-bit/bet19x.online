<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bonus;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index()
    {
        $bonuses = Bonus::latest()->paginate(15);

        return view('admin.bonuses.index', compact('bonuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,cashback,daily,referral,vip,other',
            'value_type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'max_bonus' => 'nullable|numeric|min:0',
            'min_deposit' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = true;
        $bonus = Bonus::create($data);

        AuditLog::record('Bonus Management', 'Create Bonus', "Created bonus: {$bonus->name}");

        return back()->with('status', 'Bonus create ho gaya.');
    }

    public function update(Request $request, Bonus $bonus)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,cashback,daily,referral,vip,other',
            'value_type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'max_bonus' => 'nullable|numeric|min:0',
            'min_deposit' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $bonus->update($data);

        AuditLog::record('Bonus Management', 'Update Bonus', "Updated bonus #{$bonus->id}");

        return back()->with('status', 'Bonus update ho gaya.');
    }

    public function toggle(Bonus $bonus)
    {
        $bonus->update(['is_active' => ! $bonus->is_active]);

        return back()->with('status', 'Bonus status update ho gaya.');
    }

    public function destroy(Bonus $bonus)
    {
        $bonus->delete();

        AuditLog::record('Bonus Management', 'Delete Bonus', "Deleted bonus #{$bonus->id}");

        return back()->with('status', 'Bonus delete ho gaya.');
    }
}
