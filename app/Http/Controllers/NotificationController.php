<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Show notification settings
     */
    public function settings()
    {
        $clientId = session('client_id', 1);
        $userId = Auth::id();

        $settings = NotificationSetting::getForClient($clientId, $userId);

        return view('notifications.settings', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'low_balance_enabled' => 'boolean',
            'low_balance_threshold' => 'nullable|numeric|min:0',
            'failed_delivery_enabled' => 'boolean',
            'failed_delivery_threshold' => 'nullable|integer|min:1',
            'daily_summary_enabled' => 'boolean',
            'daily_summary_time' => 'nullable|date_format:H:i',
            'weekly_summary_enabled' => 'boolean',
            'weekly_summary_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'campaign_complete_enabled' => 'boolean',
            'large_campaign_warning_enabled' => 'boolean',
            'large_campaign_threshold' => 'nullable|integer|min:1',
            'notify_via_email' => 'boolean',
            'notify_via_sms' => 'boolean',
            'notify_via_browser' => 'boolean',
        ]);

        $clientId = session('client_id', 1);
        $userId = Auth::id();

        $settings = NotificationSetting::getForClient($clientId, $userId);
        $settings->update($validated);

        return redirect()->route('notifications.settings')
            ->with('success', 'Notification settings updated successfully');
    }

    /**
     * Get unread notifications count (AJAX)
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Get notifications (AJAX)
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);

        $notifications = $user->notifications()
            ->take($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Delete a notification
     */
    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
