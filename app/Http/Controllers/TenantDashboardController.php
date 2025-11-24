<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Tag;
use App\Models\Notification as CustomNotification;

class TenantDashboardController extends Controller
{
    /**
     * Show tenant dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('login')->with('error', 'No client associated with your account.');
        }
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($client);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($client);
        
        // Get upcoming scheduled campaigns
        $scheduledCampaigns = $this->getScheduledCampaigns($client);
        
        // Get low balance warning
        $lowBalanceWarning = $this->getLowBalanceWarning($client);
        
        return view('tenant.dashboard', compact(
            'user', 
            'client', 
            'stats', 
            'recentActivity', 
            'scheduledCampaigns',
            'lowBalanceWarning'
        ));
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($client)
    {
        $stats = [
            'total_contacts' => Contact::where('client_id', $client->id)->count(),
            'total_campaigns' => Campaign::where('client_id', $client->id)->count(),
            'total_messages_sent' => Message::where('client_id', $client->id)->count(),
            'messages_this_month' => Message::where('client_id', $client->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'balance' => $client->balance,
            'balance_in_units' => $client->getBalanceInUnits(),
            'onfon_balance' => $client->onfon_balance ?? 0,
            'success_rate' => $this->calculateSuccessRate($client),
            'total_tags' => Tag::where('client_id', $client->id)->count(),
        ];
        
        return $stats;
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity($client)
    {
        $activities = collect();
        
        // Recent campaigns
        $recentCampaigns = Campaign::where('client_id', $client->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($campaign) {
                return [
                    'type' => 'campaign',
                    'title' => $campaign->name,
                    'description' => "Campaign created with {$campaign->recipients_count} recipients",
                    'time' => $campaign->created_at,
                    'icon' => 'fas fa-bullhorn',
                    'color' => 'primary',
                ];
            });
        
        // Recent messages
        $recentMessages = Message::where('client_id', $client->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($message) {
                return [
                    'type' => 'message',
                    'title' => "Message sent to {$message->recipient}",
                    'description' => "Via {$message->channel} - Status: {$message->status}",
                    'time' => $message->created_at,
                    'icon' => 'fas fa-paper-plane',
                    'color' => $message->status === 'sent' ? 'success' : 'warning',
                ];
            });
        
        // Recent contacts
        $recentContacts = Contact::where('client_id', $client->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($contact) {
                return [
                    'type' => 'contact',
                    'title' => "New contact: {$contact->name}",
                    'description' => "Phone: {$contact->phone}",
                    'time' => $contact->created_at,
                    'icon' => 'fas fa-user-plus',
                    'color' => 'info',
                ];
            });
        
        $activities = $activities
            ->merge($recentCampaigns)
            ->merge($recentMessages)
            ->merge($recentContacts)
            ->sortByDesc('time')
            ->take(10);
        
        return $activities;
    }
    
    /**
     * Get scheduled campaigns
     */
    private function getScheduledCampaigns($client)
    {
        return Campaign::where('client_id', $client->id)
            ->where('is_scheduled', true)
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();
    }
    
    /**
     * Get low balance warning
     */
    private function getLowBalanceWarning($client)
    {
        $threshold = 100; // KES 100 threshold
        $isLow = $client->balance < $threshold;
        
        return [
            'is_low' => $isLow,
            'current_balance' => $client->balance,
            'threshold' => $threshold,
            'message' => $isLow ? "Your balance is low (KES {$client->balance}). Please top up to continue sending messages." : null,
        ];
    }
    
    /**
     * Calculate success rate
     */
    private function calculateSuccessRate($client)
    {
        $totalMessages = Message::where('client_id', $client->id)->count();
        
        if ($totalMessages === 0) {
            return 0;
        }
        
        $successfulMessages = Message::where('client_id', $client->id)
            ->where('status', 'sent')
            ->count();
        
        return round(($successfulMessages / $totalMessages) * 100, 1);
    }
    
    /**
     * Show onboarding guide for new tenants
     */
    public function onboarding(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('login')->with('error', 'No client associated with your account.');
        }
        
        // Determine current step based on completion status
        $step = 1;
        
        // Step 1: Payment (if client is active, skip to step 2)
        if ($client && $client->status) {
            $step = 2;
        }
        
        // Step 2: Check if profile is complete
        if ($step == 2 && $client && $client->name && $client->email && $client->phone) {
            $step = 3;
        }
        
        // Step 3: Check if contacts exist
        if ($step == 3 && $client->contacts()->count() > 0) {
            $step = 4;
        }
        
        // Step 4: Check if first message was sent
        if ($step == 4 && $client->messages()->count() > 0) {
            $step = 5;
        }
        
        // Allow manual step override
        if ($request->has('step')) {
            $step = max(1, min(5, (int)$request->step));
        }
        
        return view('tenant.onboarding', compact('step', 'user', 'client'));
    }
    
    /**
     * Show tenant profile/settings
     */
    public function profile()
    {
        $user = Auth::user();
        $client = $user->client;
        
        return view('tenant.profile', compact('user', 'client'));
    }
    
    /**
     * Update tenant profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'required|string|max:255',
            'sender_id' => 'required|string|max:20|unique:clients,sender_id,' . $client->id,
        ]);
        
        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);
        
        // Update client
        $client->update([
            'name' => $validated['name'],
            'contact' => $validated['email'],
            'company_name' => $validated['company_name'],
            'sender_id' => $validated['sender_id'],
        ]);
        
        return redirect()->route('tenant.profile')
            ->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Show billing/balance page
     */
    public function billing()
    {
        // Redirect to the new payment page
        return redirect()->route('tenant.payment');
    }
    
    /**
     * Show API documentation for tenant
     */
    public function apiDocs()
    {
        $user = Auth::user();
        $client = $user->client;
        
        return view('tenant.api-docs', compact('user', 'client'));
    }
    
    /**
     * Show notifications
     */
    public function notifications()
    {
        $user = Auth::user();
        $notifications = CustomNotification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        return view('tenant.notifications', compact('user', 'notifications'));
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = CustomNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();
        
        if ($notification) {
            $notification->update(['read_at' => now()]);
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Delete a specific notification
     */
    public function deleteNotification(Request $request, $id)
    {
        $user = Auth::user();
        $notification = CustomNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();
        
        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Notification not found']);
    }
    
    /**
     * Clear all notifications
     */
    public function clearAllNotifications()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)->delete();
        
        return response()->json(['success' => true]);
    }
}


