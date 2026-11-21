<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $admin = Auth::guard('admin')->user();

            if (! $admin->is_active) {
                Auth::guard('admin')->logout();

                return back()->withErrors([
                    'email' => 'Aapka admin account deactivate kar diya gaya hai.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            LoginLog::create([
                'admin_id' => $admin->id,
                'ip_address' => $request->ip(),
                'device' => $request->userAgent(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email/password match nahi hua.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
