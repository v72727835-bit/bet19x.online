<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::with('parent')->withCount('users');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $agents = $query->latest()->paginate(20)->withQueryString();
        $masterAgents = Agent::where('role', 'master_agent')->orderBy('name')->get();
        $allAgents = Agent::orderBy('name')->get();

        return view('admin.agents.index', compact('agents', 'masterAgents', 'allAgents'));
    }

    public function show(Agent $agent)
    {
        $agent->load(['parent', 'children', 'users' => fn ($q) => $q->latest()->take(50)]);

        $downlineUserIds = $agent->users()->pluck('id');
        $revenue = \App\Models\ContestEntry::whereIn('user_id', $downlineUserIds)->sum('entry_fee_paid');
        $payouts = \App\Models\ContestEntry::whereIn('user_id', $downlineUserIds)->sum('prize_won');
        $netRevenue = $revenue - $payouts;
        $commissionEarned = max($netRevenue, 0) * ($agent->commission_rate / 100);

        return view('admin.agents.show', compact('agent', 'revenue', 'payouts', 'netRevenue', 'commissionEarned'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:agents,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|in:master_agent,agent,sub_agent',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'credit_limit' => 'required|numeric|min:0',
        ]);

        $data['email'] = strtolower(trim($data['email']));
        $data['password'] = Hash::make($data['password']);
        $agent = Agent::create($data);

        AuditLog::record('Agent Management', 'Create Agent', "Created {$agent->role}: {$agent->name}");

        return back()->with('status', 'Agent create ho gaya.');
    }

    public function edit(Agent $agent)
    {
        $blocked = $agent->scopeIds(); // can't become a child of itself or its own downline
        $parents = Agent::whereNotIn('id', $blocked)->orderBy('name')->get();

        return view('admin.agents.edit', compact('agent', 'parents'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'credit_limit' => 'required|numeric|min:0',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique($agent->getConnectionName() . '.agents', 'email')->ignore($agent->id)],
            'role' => 'sometimes|required|in:master_agent,agent,sub_agent',
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::notIn($agent->scopeIds())],
            'new_password' => 'nullable|string|min:6|max:100',
        ], ['parent_id.not_in' => 'Agent ko khud ke ya apne neeche wale agent ke under nahi rakh sakte.']);

        if (array_key_exists('parent_id', $data) && $data['parent_id'] && ! Agent::whereKey($data['parent_id'])->exists()) {
            return back()->withErrors(['parent_id' => 'Parent agent nahi mila.'])->withInput();
        }
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        $newPassword = $data['new_password'] ?? null;
        unset($data['new_password']);
        $agent->fill($data);
        if ($newPassword) {
            $agent->password = Hash::make($newPassword);
        }
        $agent->save();

        AuditLog::record('Agent Management', 'Update Agent', "Updated agent #{$agent->id} ({$agent->name})" . ($newPassword ? ' and set a new password' : ''));

        return redirect()->route('admin.agents.show', $agent)->with('status', $newPassword ? 'Agent update ho gaya aur naya password set ho gaya.' : 'Agent update ho gaya.');
    }

    /** Admin opens the agent panel as this agent (with that agent's own scope). */
    public function loginAs(Request $request, Agent $agent)
    {
        Auth::guard('agent')->login($agent);
        $request->session()->put('agent_impersonator_admin_id', auth('admin')->id());

        AuditLog::record('Agent Management', 'Login As Agent', "Logged in as {$agent->role} #{$agent->id} ({$agent->email})");

        return redirect()->route('agent.dashboard');
    }

    public function toggle(Agent $agent)
    {
        $agent->update(['is_active' => ! $agent->is_active]);

        AuditLog::record('Agent Management', $agent->is_active ? 'Activate Agent' : 'Deactivate Agent', $agent->name);

        return back()->with('status', 'Agent status update ho gaya.');
    }

    public function assignUser(Request $request, Agent $agent)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        $user = \App\Models\User::findOrFail($data['user_id']);
        $user->update(['agent_id' => $agent->id]);

        AuditLog::record('Agent Management', 'Assign User', "User #{$user->id} assigned to agent {$agent->name}");

        return back()->with('status', 'User agent ko assign ho gaya.');
    }

    public function destroy(Agent $agent)
    {
        if ($agent->children()->exists() || $agent->users()->exists()) {
            return back()->with('status', 'Is agent ke under users/sub-agents hain, pehle unhe reassign karo.');
        }

        AuditLog::record('Agent Management', 'Delete Agent', "{$agent->name} ({$agent->email})");
        $agent->delete();

        return redirect()->route('admin.agents.index')->with('status', 'Agent delete ho gaya.');
    }
}
