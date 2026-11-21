<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::with('sentBy')->latest()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'channel' => 'required|in:sms,email,push',
            'audience' => 'required|in:all,active,blocked,kyc_pending',
        ]);

        $query = User::query();
        if ($data['audience'] === 'active') {
            $query->where('is_blocked', false);
        } elseif ($data['audience'] === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($data['audience'] === 'kyc_pending') {
            $query->where('kyc_status', 'pending');
        }

        $recipientCount = $query->count();

        $notification = AdminNotification::create([
            ...$data,
            'recipient_count' => $recipientCount,
            'sent_by' => auth('admin')->id(),
        ]);

        // Actual SMS/Email/Push dispatch would be wired to a gateway here.
        AuditLog::record('Notifications', 'Send Notification', "Sent '{$notification->title}' to {$recipientCount} users via {$notification->channel}");

        return back()->with('status', "Notification {$recipientCount} users ko queue ho gayi.");
    }
}
