<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('admin');

        if ($module = $request->get('module')) {
            $query->where('module', $module);
        }

        $logs = $query->latest()->paginate(30)->withQueryString();
        $modules = AuditLog::select('module')->distinct()->pluck('module');

        return view('admin.security.audit-logs', compact('logs', 'modules'));
    }

    public function loginHistory(Request $request)
    {
        $query = LoginLog::with(['user', 'admin']);

        if ($ip = $request->get('ip')) {
            $query->where('ip_address', $ip);
        }

        $logs = $query->latest()->paginate(30)->withQueryString();

        return view('admin.security.login-history', compact('logs'));
    }

    public function blockedIps()
    {
        $ips = BlockedIp::with('blockedBy')->latest()->paginate(20);

        return view('admin.security.blocked-ips', compact('ips'));
    }

    public function blockIp(Request $request)
    {
        $data = $request->validate([
            'ip_address' => 'required|ip|unique:blocked_ips,ip_address',
            'reason' => 'nullable|string|max:255',
        ]);

        $data['blocked_by'] = auth('admin')->id();
        BlockedIp::create($data);

        AuditLog::record('Security', 'Block IP', "Blocked IP: {$data['ip_address']}");

        return back()->with('status', 'IP block ho gaya.');
    }

    public function unblockIp(BlockedIp $blockedIp)
    {
        $blockedIp->delete();

        AuditLog::record('Security', 'Unblock IP', "Unblocked IP: {$blockedIp->ip_address}");

        return back()->with('status', 'IP unblock ho gaya.');
    }
}
