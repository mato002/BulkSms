<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\Notification;
use App\Services\OnfonWalletService;
use App\Mail\WelcomeSenderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with all senders/tenants
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $search = $request->get('search');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);

        $query = Client::query()
            ->withCount(['campaigns', 'sms', 'contacts'])
            ->with(['users' => function($q) {
                $q->select('id', 'name', 'email', 'client_id')->limit(1);
            }]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%")
                  ->orWhere('sender_id', 'like', "%{$search}%")
                  ->orWhere('api_key', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Statistics
        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', true)->count(),
            'inactive_clients' => Client::where('status', false)->count(),
            'total_balance' => Client::sum('balance'),
            'total_messages' => Message::count(),
            'total_campaigns' => Campaign::count(),
        ];

        return view('admin.senders.index', compact('clients', 'stats'));
    }

    /**
     * Show the form for creating a new sender/tenant
     */
    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.senders.create');
    }

    /**
     * Store a newly created sender/tenant
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'sender_id' => 'required|string|max:11|unique:clients,sender_id',
            'company_name' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0.01',
            'status' => 'nullable|boolean',
            // Optional: Create admin user for this client
            'create_user' => 'nullable|boolean',
            'user_name' => 'required_if:create_user,1|string|max:255',
            'user_email' => 'required_if:create_user,1|email|unique:users,email',
            'user_password' => 'required_if:create_user,1|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Prepare settings with Onfon credentials if provided
        $settings = $request->settings ?? [];
        if ($request->has('enable_onfon') && $request->onfon_api_key && $request->onfon_client_id) {
            $settings['onfon_credentials'] = [
                'api_key' => $request->onfon_api_key,
                'client_id' => $request->onfon_client_id,
                'access_key_header' => $request->onfon_access_key ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
                'default_sender' => $request->default_sender ?? strtoupper($request->sender_id),
            ];
        }

        // Create client
        $client = Client::create([
            'name' => $request->name,
            'contact' => $request->contact,
            'sender_id' => strtoupper($request->sender_id),
            'company_name' => $request->company_name ?? strtoupper($request->sender_id),
            'balance' => $request->balance ?? 0,
            'price_per_unit' => $request->price_per_unit ?? 1.00,
            'api_key' => $this->generateApiKey(),
            'status' => $request->status ?? true,
            'settings' => $settings,
            'auto_sync_balance' => $request->has('auto_sync_balance') && $request->auto_sync_balance,
        ]);

        // Create user for client if requested
        if ($request->create_user) {
            User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'password' => Hash::make($request->user_password),
                'client_id' => $client->id,
                'role' => 'user',
            ]);
        }

        // Send welcome email
        try {
            if ($client->contact && filter_var($client->contact, FILTER_VALIDATE_EMAIL)) {
                Mail::to($client->contact)->send(new WelcomeSenderMail($client));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send welcome email', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
        }

        // Create notification for sender creation
        try {
            Notification::senderCreated($client->id, $client->name, $client->sender_id, auth()->id());
        } catch (\Exception $e) {
            \Log::error('Failed to create sender creation notification', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.senders.show', $client->id)
            ->with('success', 'Sender created successfully! API Key: ' . $client->api_key);
    }

    /**
     * Display the specified sender/tenant
     */
    public function show($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::with(['users', 'campaigns', 'contacts'])
            ->withCount(['campaigns', 'sms', 'contacts'])
            ->findOrFail($id);

        // Get recent messages
        $recentMessages = Message::where('client_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get statistics for this client
        $stats = [
            'total_messages' => Message::where('client_id', $id)->count(),
            'delivered_messages' => Message::where('client_id', $id)->where('status', 'delivered')->count(),
            'failed_messages' => Message::where('client_id', $id)->where('status', 'failed')->count(),
            'total_campaigns' => Campaign::where('client_id', $id)->count(),
            'total_contacts' => $client->contacts_count,
        ];

        return view('admin.senders.show', compact('client', 'recentMessages', 'stats'));
    }

    /**
     * Show the form for editing the specified sender/tenant
     */
    public function edit($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::with('users')->findOrFail($id);
        return view('admin.senders.edit', compact('client'));
    }

    /**
     * Update the specified sender/tenant
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'sender_id' => 'required|string|max:11|unique:clients,sender_id,' . $id,
            'balance' => 'nullable|numeric|min:0',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $changes = [];
        $oldName = $client->name;
        $oldContact = $client->contact;
        $oldSenderId = $client->sender_id;
        $oldStatus = $client->status;

        $client->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'sender_id' => strtoupper($request->sender_id),
            'balance' => $request->balance ?? $client->balance,
            'status' => $request->has('status') ? $request->status : $client->status,
        ]);

        // Track changes
        if ($oldName !== $request->name) $changes[] = 'name';
        if ($oldContact !== $request->contact) $changes[] = 'contact';
        if ($oldSenderId !== strtoupper($request->sender_id)) $changes[] = 'sender_id';
        if ($oldStatus != $client->status) {
            try {
                Notification::senderStatusChanged($client->id, $client->name, $oldStatus, $client->status, auth()->id());
            } catch (\Exception $e) {
                \Log::error('Failed to create status change notification', ['error' => $e->getMessage()]);
            }
        }

        // Create notification for sender update
        if (!empty($changes)) {
            try {
                Notification::senderUpdated($client->id, $client->name, $changes, auth()->id());
            } catch (\Exception $e) {
                \Log::error('Failed to create sender update notification', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.senders.show', $client->id)
            ->with('success', 'Sender updated successfully!');
    }

    /**
     * Remove the specified sender/tenant
     */
    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::findOrFail($id);
        
        // Prevent deletion of default client
        if ($client->id == 1) {
            return back()->with('error', 'Cannot delete the default client!');
        }

        $senderName = $client->name;
        $senderId = $client->sender_id;
        $deletedBy = auth()->id();

        $client->delete();

        // Create notification for sender deletion
        try {
            Notification::senderDeleted($senderName, $senderId, $deletedBy);
        } catch (\Exception $e) {
            \Log::error('Failed to create sender deletion notification', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.senders.index')
            ->with('success', 'Sender deleted successfully!');
    }

    /**
     * Regenerate API key for a sender
     */
    public function regenerateApiKey($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::findOrFail($id);
        $newApiKey = $this->generateApiKey();
        
        $client->update(['api_key' => $newApiKey]);

        // Create notification for API key regeneration
        try {
            Notification::apiKeyRegenerated($client->id, $client->name, auth()->id());
        } catch (\Exception $e) {
            \Log::error('Failed to create API key regeneration notification', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'API Key regenerated successfully! New Key: ' . $newApiKey);
    }

    /**
     * Update balance for a sender
     */
    public function updateBalance(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:add,deduct,set',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:units,ksh',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $client = Client::findOrFail($id);
        $isUnits = $request->type === 'units';
        $amountInKsh = $isUnits ? $client->unitsToKsh($request->amount) : $request->amount;
        $oldBalance = $client->balance;

        switch ($request->action) {
            case 'add':
                $client->balance += $amountInKsh;
                break;
            case 'deduct':
                $client->balance = max(0, $client->balance - $amountInKsh);
                break;
            case 'set':
                $client->balance = $amountInKsh;
                break;
        }

        $client->save();

        // Create notification for balance change
        try {
            Notification::balanceChanged($client->id, $client->name, $request->action, $amountInKsh, $oldBalance, $client->balance, auth()->id());
        } catch (\Exception $e) {
            \Log::error('Failed to create balance change notification', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Balance updated successfully! New balance: ' . number_format($client->balance, 2));
    }

    /**
     * Toggle sender status (active/inactive)
     */
    public function toggleStatus($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::findOrFail($id);
        $oldStatus = $client->status;
        $client->status = !$client->status;
        $client->save();

        // Create notification for status change
        try {
            Notification::senderStatusChanged($client->id, $client->name, $oldStatus, $client->status, auth()->id());
        } catch (\Exception $e) {
            \Log::error('Failed to create status change notification', ['error' => $e->getMessage()]);
        }

        $status = $client->status ? 'activated' : 'deactivated';
        return back()->with('success', "Sender {$status} successfully!");
    }

    /**
     * Generate a unique API key
     */
    private function generateApiKey(): string
    {
        do {
            $apiKey = 'sk_' . Str::random(32);
        } while (Client::where('api_key', $apiKey)->exists());

        return $apiKey;
    }

    /**
     * Update Onfon credentials for a sender
     */
    public function updateOnfonCredentials(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'onfon_api_key' => 'required|string',
            'onfon_client_id' => 'required|string',
            'onfon_access_key' => 'nullable|string',
            'default_sender' => 'nullable|string|max:11',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $client = Client::findOrFail($id);

        // Store Onfon credentials in settings
        $settings = $client->settings ?? [];
        $settings['onfon_credentials'] = [
            'api_key' => $request->onfon_api_key,
            'client_id' => $request->onfon_client_id,
            'access_key_header' => $request->onfon_access_key ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB',
            'default_sender' => $request->default_sender ?? $client->sender_id,
        ];

        $client->settings = $settings;
        $client->auto_sync_balance = $request->has('auto_sync_balance');
        $client->save();

        // Create notification for Onfon credentials update
        try {
            Notification::onfonCredentialsUpdated($client->id, $client->name, auth()->id());
        } catch (\Exception $e) {
            \Log::error('Failed to create Onfon credentials update notification', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Onfon credentials updated successfully!');
    }

    /**
     * Sync balance from Onfon Media
     */
    public function syncOnfonBalance(Request $request, $id, OnfonWalletService $walletService)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::findOrFail($id);
        $result = $walletService->syncBalance($client);

        if ($result['success']) {
            return back()->with('success', sprintf(
                'Balance synced successfully! Old: KES %.2f | New: KES %.2f | Difference: KES %.2f',
                $result['old_balance'],
                $result['new_balance'],
                $result['difference']
            ));
        }

        return back()->with('error', $result['message'] ?? 'Failed to sync balance from Onfon');
    }

    /**
     * Get Onfon balance (AJAX)
     */
    public function getOnfonBalance(Request $request, $id, OnfonWalletService $walletService)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $client = Client::findOrFail($id);
        $result = $walletService->getBalance($client);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'balance' => $result['balance'],
                'currency' => $result['currency'] ?? 'KES',
                'units' => $result['units'] ?? 0,
                'last_sync' => $client->onfon_last_sync ? $client->onfon_last_sync->diffForHumans() : 'Never'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to fetch balance'
        ], 400);
    }

    /**
     * Test Onfon connection
     */
    public function testOnfonConnection(Request $request, $id, OnfonWalletService $walletService)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $client = Client::findOrFail($id);
        $result = $walletService->testConnection($client);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'balance' => $result['balance'] ?? 0,
                'currency' => $result['currency'] ?? 'KES',
                'units' => $result['units'] ?? 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Connection failed'
        ], 400);
    }

    /**
     * Get Onfon transaction history
     */
    public function getOnfonTransactions(Request $request, $id, OnfonWalletService $walletService)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $client = Client::findOrFail($id);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $result = $walletService->getTransactionHistory($client, $fromDate, $toDate);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'transactions' => $result['transactions'] ?? []
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to fetch transactions'
        ], 400);
    }

    /**
     * Display list of admin users
     */
    public function admins(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = User::where('role', 'admin')
            ->where('client_id', 1); // Admin users belong to client_id 1

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show form to create a new admin user
     */
    public function createAdmin()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.admins.create');
    }

    /**
     * Store a new admin user
     */
    public function storeAdmin(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'client_id' => 1, // Admin users belong to client_id 1
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create notification for admin creation
        try {
            Notification::adminCreated($admin->id, $admin->name, $admin->email, auth()->id());
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to create admin creation notification', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user created successfully!');
    }

    /**
     * Show form to edit an admin user
     */
    public function editAdmin($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $admin = User::where('role', 'admin')
            ->where('client_id', 1)
            ->findOrFail($id);

        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update an admin user
     */
    public function updateAdmin(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $admin = User::where('role', 'admin')
            ->where('client_id', 1)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $changes = [];
        $oldName = $admin->name;
        $oldEmail = $admin->email;

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $changes[] = 'password';
        }

        if ($oldName !== $request->name) {
            $changes[] = 'name';
        }
        if ($oldEmail !== $request->email) {
            $changes[] = 'email';
        }

        $admin->update($updateData);

        // Create notification for admin update
        if (!empty($changes)) {
            try {
                Notification::adminUpdated($admin->id, $admin->name, $changes, auth()->id());
            } catch (\Exception $e) {
                \Log::error('Failed to create admin update notification', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user updated successfully!');
    }

    /**
     * Delete an admin user
     */
    public function destroyAdmin($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $admin = User::where('role', 'admin')
            ->where('client_id', 1)
            ->findOrFail($id);

        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        // Prevent deleting if it's the only admin
        $adminCount = User::where('role', 'admin')
            ->where('client_id', 1)
            ->count();

        if ($adminCount <= 1) {
            return back()->with('error', 'Cannot delete the last admin user!');
        }

        $adminName = $admin->name;
        $adminEmail = $admin->email;
        $deletedBy = auth()->id();

        $admin->delete();

        // Create notification for admin deletion
        try {
            Notification::adminDeleted($adminName, $adminEmail, $deletedBy);
        } catch (\Exception $e) {
            \Log::error('Failed to create admin deletion notification', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user deleted successfully!');
    }
}

