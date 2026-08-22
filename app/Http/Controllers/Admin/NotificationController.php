<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('order')->orderBy('id', 'desc')->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        if ($notification->order_id) {
            return redirect()->route('admin.orders.show', $notification->order_id);
        }
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
