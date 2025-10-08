<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the authenticated user's client ID.
     */
    private function getClientId()
    {
        $user = Auth::user();
        
        // If user has a client_id, use it; otherwise default to 1 for backwards compatibility
        return $user && $user->client_id ? $user->client_id : 1;
    }

    /**
     * Get recent notifications for the current client.
     */
    public function index(Request $request)
    {
        $clientId = $this->getClientId();
        
        $notifications = Notification::forClient($clientId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::forClient($clientId)->unread()->count()
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $clientId = $this->getClientId();
        
        return response()->json([
            'count' => Notification::forClient($clientId)->unread()->count()
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $clientId = $this->getClientId();
        
        $notification = Notification::forClient($clientId)->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $clientId = $this->getClientId();
        
        Notification::forClient($clientId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $clientId = $this->getClientId();
        
        $notification = Notification::forClient($clientId)->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
}
