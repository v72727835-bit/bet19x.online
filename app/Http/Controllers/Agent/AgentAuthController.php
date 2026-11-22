<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AgentAuthController extends Controller
{
    public function showLogin()
    {
        return view('agent.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'agent-login:' . Str::lower($data['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return back()->withErrors(['email' => 'Bahut zyada galat koshish. ' . RateLimiter::availableIn($key) . ' second baad try karein.'])->onlyInput('email');
        }

        $agent = Agent::where('email', Str::lower(trim($data['email'])))->first()
            ?? Agent::where('email', trim($data['email']))->first();

        if (! $agent || ! Auth::guard('agent')->validate(['email' => $agent->email, 'password' => $data['password']])) {
            RateLimiter::hit($key, 300);
            return back()->withErrors(['email' => 'Email ya password galat hai.'])->onlyInput('email');
        }
        if (! $agent->is_active) {
            return back()->withErrors(['email' => 'Aapka agent account deactivate hai. Admin se sampark karein.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        Auth::guard('agent')->login($agent);
        $request->session()->regenerate();
        $request->session()->forget('agent_impersonator_admin_id');
        $agent->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('agent.dashboard');
    }

    public function logout(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        $byAdmin = $request->session()->pull('agent_impersonator_admin_id');
        Auth::guard('agent')->logout();

        if ($byAdmin && auth('admin')->check()) {
            return redirect()->route('admin.agents.show', $agentId);
        }

        return redirect()->route('agent.login');
    }
}
