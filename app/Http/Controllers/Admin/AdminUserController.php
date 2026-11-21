<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->paginate(15);

        return view('admin.admin-users.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,manager,support',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;
        $admin = Admin::create($data);

        AuditLog::record('Admin Users', 'Create Admin', "Created admin: {$admin->email} ({$admin->role})");

        return back()->with('status', 'Naya admin user create ho gaya.');
    }

    public function toggle(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('status', 'Aap khud ko deactivate nahi kar sakte.');
        }

        $admin->update(['is_active' => ! $admin->is_active]);

        AuditLog::record('Admin Users', 'Toggle Admin', "Admin #{$admin->id} active: {$admin->is_active}");

        return back()->with('status', 'Admin status update ho gaya.');
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('status', 'Aap khud ko delete nahi kar sakte.');
        }

        $admin->delete();

        AuditLog::record('Admin Users', 'Delete Admin', "Deleted admin #{$admin->id}");

        return back()->with('status', 'Admin delete ho gaya.');
    }
}
