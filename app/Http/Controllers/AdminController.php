<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\Message;
use App\Models\Campaign;
use App\Services\OnfonWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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
            'settings' => $request->settings ?? []
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

        $client->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'sender_id' => strtoupper($request->sender_id),
            'balance' => $request->balance ?? $client->balance,
            'status' => $request->has('status') ? $request->status : $client->status,
        ]);

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

        $client->delete();

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
        $client->status = !$client->status;
        $client->save();

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
}

