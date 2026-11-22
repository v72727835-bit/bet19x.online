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
            'email' => 'required|email|unique:' . (new Admin)->getConnectionName() . '.admins,email',
            'password' => 'required|string|min:8',
            'type' => 'required|in:super_admin,sub_admin',
            'permissions' => 'array',
            'permissions.*' => 'in:' . implode(',', array_keys(Admin::MODULES)),
        ]);

        $isSuper = $data['type'] === 'super_admin';
        if (! $isSuper && empty($data['permissions'])) {
            return back()->withInput()->withErrors(['permissions' => 'Sub-admin ke liye kam se kam ek permission chunein.']);
        }

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $isSuper ? 'super_admin' : 'support',
            'permissions' => $isSuper ? null : array_values($data['permissions']),
            'is_active' => true,
        ]);

        AuditLog::record('Admin Users', 'Create Admin', "Created {$admin->roleLabel()}: {$admin->email} [" . implode(', ', $admin->modules()) . ']');

        return back()->with('status', "{$admin->roleLabel()} {$admin->email} create ho gaya.");
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'in:' . implode(',', array_keys(Admin::MODULES)),
            'new_password' => 'nullable|string|min:8',
        ]);

        if ($admin->isSuperAdmin()) {
            return back()->with('status', 'Super admin ke paas pehle se saari permissions hain.');
        }
        if (empty($data['permissions'])) {
            return back()->withErrors(['permissions' => 'Kam se kam ek permission chunein.']);
        }

        $admin->permissions = array_values($data['permissions']);
        if (! empty($data['new_password'])) {
            $admin->password = Hash::make($data['new_password']);
        }
        $admin->save();

        AuditLog::record('Admin Users', 'Update Admin', "Updated admin #{$admin->id} permissions: " . implode(', ', $admin->permissions));

        return back()->with('status', "{$admin->email} ki permissions update ho gayi.");
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
