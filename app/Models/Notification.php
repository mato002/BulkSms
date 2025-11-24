<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'link',
        'is_read',
        'read_at',
        'metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the client that owns the notification.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include notifications for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
    }

    /**
     * Create a notification for a campaign completion.
     * Creates notifications for all users in the client
     */
    public static function campaignCompleted($clientId, $campaignId, $campaignName, $totalMessages, $sentCount = null, $failedCount = null)
    {
        $users = \App\Models\User::where('client_id', $clientId)->get();
        $notifications = [];
        
        $message = "Campaign '{$campaignName}' has been completed. {$totalMessages} messages sent.";
        if ($sentCount !== null && $failedCount !== null) {
            $message = "Campaign '{$campaignName}' has been completed. {$sentCount} succeeded, {$failedCount} failed.";
        }
        
        foreach ($users as $user) {
            $notifications[] = self::create([
                'client_id' => $clientId,
                'user_id' => $user->id,
                'type' => 'campaign_completed',
                'title' => 'Campaign Completed',
                'message' => $message,
                'icon' => 'bi-megaphone-fill',
                'color' => ($failedCount && $failedCount > 0) ? 'warning' : 'success',
                'link' => route('campaigns.show', $campaignId),
                'metadata' => [
                    'campaign_id' => $campaignId,
                    'total_messages' => $totalMessages,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                ]
            ]);
        }
        
        return $notifications;
    }

    /**
     * Create a notification for message failures.
     * Creates notifications for all users in the client
     */
    public static function messagesFailed($clientId, $count, $reason = null)
    {
        $users = \App\Models\User::where('client_id', $clientId)->get();
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = self::create([
                'client_id' => $clientId,
                'user_id' => $user->id,
                'type' => 'messages_failed',
                'title' => 'Messages Failed',
                'message' => "{$count} message(s) failed to send" . ($reason ? ": {$reason}" : '.'),
                'icon' => 'bi-exclamation-triangle-fill',
                'color' => 'danger',
                'link' => route('messages.index', ['status' => 'failed']),
                'metadata' => [
                    'count' => $count,
                    'reason' => $reason
                ]
            ]);
        }
        
        return $notifications;
    }

    /**
     * Create a notification for low balance
     * Creates notifications for all users in the client
     */
    public static function lowBalance($clientId, $balance, $threshold)
    {
        $users = \App\Models\User::where('client_id', $clientId)->get();
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = self::create([
                'client_id' => $clientId,
                'user_id' => $user->id,
                'type' => 'low_balance',
                'title' => 'Low Balance Alert',
                'message' => "Your balance (KES " . number_format($balance, 2) . ") is below the threshold (KES " . number_format($threshold, 2) . ").",
                'icon' => 'bi-exclamation-triangle-fill',
                'color' => 'warning',
                'link' => route('wallet.index'),
                'metadata' => [
                    'balance' => $balance,
                    'threshold' => $threshold,
                ]
            ]);
        }
        
        return $notifications;
    }

    /**
     * Create a notification for new message received
     * Creates notifications for all users in the client
     */
    public static function newMessage($clientId, $contactName, $messagePreview, $conversationId, $channel = 'sms')
    {
        $users = \App\Models\User::where('client_id', $clientId)->get();
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = self::create([
                'client_id' => $clientId,
                'user_id' => $user->id,
                'type' => 'new_message',
                'title' => 'New Message Received',
                'message' => "New {$channel} message from {$contactName}: " . substr($messagePreview, 0, 50) . "...",
                'icon' => $channel === 'whatsapp' ? 'bi-whatsapp' : 'bi-chat-dots',
                'color' => $channel === 'whatsapp' ? 'success' : 'primary',
                'link' => route('inbox.show', $conversationId),
                'metadata' => [
                    'conversation_id' => $conversationId,
                    'channel' => $channel,
                    'contact_name' => $contactName,
                    'message_preview' => $messagePreview,
                ]
            ]);
        }
        
        return $notifications;
    }

    /**
     * Create a notification for admin user creation
     */
    public static function adminCreated($adminId, $adminName, $adminEmail, $createdBy)
    {
        $user = \App\Models\User::find($createdBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $adminId,
            'type' => 'admin_created',
            'title' => 'Admin User Created',
            'message' => "New admin user '{$adminName}' ({$adminEmail}) was created by {$user->name}",
            'icon' => 'bi-person-plus-fill',
            'color' => 'warning',
            'link' => route('admin.admins.index'),
            'metadata' => [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'admin_email' => $adminEmail,
                'created_by' => $createdBy,
                'created_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for admin user update
     */
    public static function adminUpdated($adminId, $adminName, $changes, $updatedBy)
    {
        $user = \App\Models\User::find($updatedBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $adminId,
            'type' => 'admin_updated',
            'title' => 'Admin User Updated',
            'message' => "Admin user '{$adminName}' was updated by {$user->name}. Changes: " . implode(', ', $changes),
            'icon' => 'bi-person-check-fill',
            'color' => 'info',
            'link' => route('admin.admins.index'),
            'metadata' => [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'changes' => $changes,
                'updated_by' => $updatedBy,
                'updated_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for admin user deletion
     */
    public static function adminDeleted($adminName, $adminEmail, $deletedBy)
    {
        $user = \App\Models\User::find($deletedBy);
        // Notify all admins
        $admins = \App\Models\User::where('role', 'admin')->where('client_id', 1)->get();
        $notifications = [];
        foreach ($admins as $admin) {
            $notifications[] = self::create([
                'client_id' => 1,
                'user_id' => $admin->id,
                'type' => 'admin_deleted',
                'title' => 'Admin User Deleted',
                'message' => "Admin user '{$adminName}' ({$adminEmail}) was deleted by {$user->name}",
                'icon' => 'bi-person-x-fill',
                'color' => 'danger',
                'link' => route('admin.admins.index'),
                'metadata' => [
                    'admin_name' => $adminName,
                    'admin_email' => $adminEmail,
                    'deleted_by' => $deletedBy,
                    'deleted_by_name' => $user->name,
                    'ip' => request()->ip(),
                ]
            ]);
        }
        return $notifications;
    }

    /**
     * Create a notification for sender/client creation
     */
    public static function senderCreated($clientId, $senderName, $senderId, $createdBy)
    {
        $user = \App\Models\User::find($createdBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $createdBy,
            'type' => 'sender_created',
            'title' => 'New Sender Created',
            'message' => "New sender '{$senderName}' (ID: {$senderId}) was created by {$user->name}",
            'icon' => 'bi-building-add',
            'color' => 'success',
            'link' => route('admin.senders.show', $clientId),
            'metadata' => [
                'client_id' => $clientId,
                'sender_name' => $senderName,
                'sender_id' => $senderId,
                'created_by' => $createdBy,
                'created_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for sender/client update
     */
    public static function senderUpdated($clientId, $senderName, $changes, $updatedBy)
    {
        $user = \App\Models\User::find($updatedBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $updatedBy,
            'type' => 'sender_updated',
            'title' => 'Sender Updated',
            'message' => "Sender '{$senderName}' was updated by {$user->name}. Changes: " . implode(', ', $changes),
            'icon' => 'bi-building-check',
            'color' => 'info',
            'link' => route('admin.senders.show', $clientId),
            'metadata' => [
                'client_id' => $clientId,
                'sender_name' => $senderName,
                'changes' => $changes,
                'updated_by' => $updatedBy,
                'updated_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for sender/client deletion
     */
    public static function senderDeleted($senderName, $senderId, $deletedBy)
    {
        $user = \App\Models\User::find($deletedBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $deletedBy,
            'type' => 'sender_deleted',
            'title' => 'Sender Deleted',
            'message' => "Sender '{$senderName}' (ID: {$senderId}) was deleted by {$user->name}",
            'icon' => 'bi-building-x',
            'color' => 'danger',
            'link' => route('admin.senders.index'),
            'metadata' => [
                'sender_name' => $senderName,
                'sender_id' => $senderId,
                'deleted_by' => $deletedBy,
                'deleted_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for balance changes
     */
    public static function balanceChanged($clientId, $senderName, $action, $amount, $oldBalance, $newBalance, $changedBy)
    {
        $user = \App\Models\User::find($changedBy);
        $actionText = ucfirst($action);
        return self::create([
            'client_id' => 1,
            'user_id' => $changedBy,
            'type' => 'balance_changed',
            'title' => 'Balance Changed',
            'message' => "Balance {$actionText}: KES " . number_format($amount, 2) . " for '{$senderName}'. New balance: KES " . number_format($newBalance, 2),
            'icon' => 'bi-cash-stack',
            'color' => $action === 'deduct' ? 'warning' : ($action === 'add' ? 'success' : 'info'),
            'link' => route('admin.senders.show', $clientId),
            'metadata' => [
                'client_id' => $clientId,
                'sender_name' => $senderName,
                'action' => $action,
                'amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'changed_by' => $changedBy,
                'changed_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for API key regeneration
     */
    public static function apiKeyRegenerated($clientId, $senderName, $regeneratedBy)
    {
        $user = \App\Models\User::find($regeneratedBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $regeneratedBy,
            'type' => 'api_key_regenerated',
            'title' => 'API Key Regenerated',
            'message' => "API key for '{$senderName}' was regenerated by {$user->name}",
            'icon' => 'bi-key-fill',
            'color' => 'warning',
            'link' => route('admin.senders.show', $clientId),
            'metadata' => [
                'client_id' => $clientId,
                'sender_name' => $senderName,
                'regenerated_by' => $regeneratedBy,
                'regenerated_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for sender status change
     */
    public static function senderStatusChanged($clientId, $senderName, $oldStatus, $newStatus, $changedBy)
    {
        $user = \App\Models\User::find($changedBy);
        $statusText = $newStatus ? 'activated' : 'deactivated';
        return self::create([
            'client_id' => 1,
            'user_id' => $changedBy,
            'type' => 'sender_status_changed',
            'title' => 'Sender Status Changed',
            'message' => "Sender '{$senderName}' was {$statusText} by {$user->name}",
            'icon' => $newStatus ? 'bi-check-circle-fill' : 'bi-x-circle-fill',
            'color' => $newStatus ? 'success' : 'danger',
            'link' => route('admin.senders.show', $clientId),
            'metadata' => [
                'client_id' => $clientId,
                'sender_name' => $senderName,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy,
                'changed_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for password change
     */
    public static function passwordChanged($userId, $userName, $changedBy = null)
    {
        $changedByUser = $changedBy ? \App\Models\User::find($changedBy) : null;
        $message = $changedByUser && $changedByUser->id !== $userId 
            ? "Password changed by {$changedByUser->name}" 
            : "Password changed";
        
        return self::create([
            'client_id' => \App\Models\User::find($userId)->client_id ?? 1,
            'user_id' => $userId,
            'type' => 'password_changed',
            'title' => 'Password Changed',
            'message' => $message,
            'icon' => 'bi-shield-lock-fill',
            'color' => 'warning',
            'link' => route('profile.show'),
            'metadata' => [
                'user_id' => $userId,
                'user_name' => $userName,
                'changed_by' => $changedBy,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for Onfon credentials update
     */
    public static function onfonCredentialsUpdated($clientId, $senderName, $updatedBy)
    {
        $user = \App\Models\User::find($updatedBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $updatedBy,
            'type' => 'onfon_credentials_updated',
            'title' => 'Onfon Credentials Updated',
            'message' => "Onfon credentials for '{$senderName}' were updated by {$user->name}",
            'icon' => 'bi-cloud-upload-fill',
            'color' => 'info',
            'link' => route('admin.senders.show', $clientId),
            'metadata' => [
                'client_id' => $clientId,
                'sender_name' => $senderName,
                'updated_by' => $updatedBy,
                'updated_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }

    /**
     * Create a notification for settings changes
     */
    public static function settingsChanged($settingsChanged, $changedBy)
    {
        $user = \App\Models\User::find($changedBy);
        return self::create([
            'client_id' => 1,
            'user_id' => $changedBy,
            'type' => 'settings_changed',
            'title' => 'Settings Updated',
            'message' => "System settings were updated by {$user->name}. Changed: " . implode(', ', $settingsChanged),
            'icon' => 'bi-gear-fill',
            'color' => 'info',
            'link' => route('settings.index'),
            'metadata' => [
                'settings_changed' => $settingsChanged,
                'changed_by' => $changedBy,
                'changed_by_name' => $user->name,
                'ip' => request()->ip(),
            ]
        ]);
    }
}
