<?php

namespace App\Http\Controllers\Master\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterLoginController extends Controller
{
    public function showLoginForm()
    {
        $masterSetting = \App\Models\MasterSetting::first();

        return view('master.auth.login', compact('masterSetting'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('master_admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('master.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email/password match nahi hua.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('master_admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('master.login');
    }
}
