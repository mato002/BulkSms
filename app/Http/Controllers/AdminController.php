<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\WalletTransaction;
use App\Services\OnfonWalletService;
use App\Mail\WelcomeSenderMail;
use App\Support\CsvExporter;
use App\Support\ListFilters;
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

        $query = Client::query()
            ->withCount(['campaigns', 'messages', 'contacts'])
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

        $clients = ListFilters::paginate(
            $query->orderBy('created_at', 'desc'),
            $request,
            25
        );

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
    public function show(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $client = Client::with(['users', 'campaigns', 'contacts'])
            ->withCount(['campaigns', 'messages', 'contacts'])
            ->findOrFail($id);

        $perPage = ListFilters::perPage($request, 25);

        $messagesQuery = Message::where('client_id', $id);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $messagesQuery->where(function ($q) use ($search) {
                $q->where('recipient', 'like', $search)
                    ->orWhere('body', 'like', $search)
                    ->orWhere('sender', 'like', $search);
            });
        }

        if ($request->filled('channel')) {
            $messagesQuery->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $messagesQuery->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $messagesQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $messagesQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $messagesQuery
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total_messages' => Message::where('client_id', $id)->count(),
            'delivered_messages' => Message::where('client_id', $id)->where('status', 'delivered')->count(),
            'failed_messages' => Message::where('client_id', $id)->where('status', 'failed')->count(),
            'total_campaigns' => Campaign::where('client_id', $id)->count(),
            'total_contacts' => $client->contacts_count,
        ];

        $returnUrl = route('admin.senders.show', $client->id);
        if ($request->query()) {
            $returnUrl .= '?' . http_build_query($request->query());
        }

        return view('admin.senders.show', compact('client', 'messages', 'stats', 'returnUrl', 'perPage'));
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
            'price_per_unit' => 'nullable|numeric|min:0.01',
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
        $oldPricePerUnit = $client->price_per_unit;

        $settings = $client->settings ?? [];
        $settings['append_reply_link'] = $request->boolean('append_reply_link');
        $settings['append_stop_footer'] = $request->boolean('append_stop_footer');

        $client->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'sender_id' => strtoupper($request->sender_id),
            'balance' => $request->balance ?? $client->balance,
            'price_per_unit' => $request->price_per_unit ?? $client->price_per_unit,
            'status' => $request->has('status') ? $request->status : $client->status,
            'settings' => $settings,
        ]);

        // Track changes
        if ($oldName !== $request->name) $changes[] = 'name';
        if ($oldContact !== $request->contact) $changes[] = 'contact';
        if ($oldSenderId !== strtoupper($request->sender_id)) $changes[] = 'sender_id';
        if ((float) $oldPricePerUnit !== (float) $client->price_per_unit) $changes[] = 'price_per_unit';
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
     * All wallet payments across senders (admin ledger).
     */
    public function payments(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $transactions = ListFilters::paginate(
            $this->buildPaymentsQuery($request),
            $request,
            25
        );

        $senders = Client::orderBy('sender_id')->get(['id', 'sender_id', 'name']);

        $statsQuery = WalletTransaction::query();
        if ($request->filled('client_id')) {
            $statsQuery->where('client_id', $request->client_id);
        }

        $stats = [
            'credits_today' => (clone $statsQuery)->where('type', 'credit')
                ->where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'credits_month' => (clone $statsQuery)->where('type', 'credit')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'pending_count' => (clone $statsQuery)->whereIn('status', ['pending', 'processing'])->count(),
            'total_credits' => (clone $statsQuery)->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('admin.payments.index', compact('transactions', 'senders', 'stats'));
    }

    /**
     * Export all wallet payments as CSV.
     */
    public function exportPayments(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $transactions = $this->buildPaymentsQuery($request)->get();
        $filename = 'all_payments_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Sender ID',
                'Sender Name',
                'Transaction ID',
                'Type',
                'Source',
                'Reference',
                'External Reference',
                'Description',
                'Amount (KES)',
                'Status',
                'M-Pesa Receipt',
                'Phone',
            ]);

            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->created_at->format('Y-m-d H:i:s'),
                    $txn->client?->sender_id ?? 'N/A',
                    $txn->client?->name ?? 'N/A',
                    $txn->transaction_ref,
                    ucfirst($txn->type),
                    $txn->source_label,
                    $txn->transaction_ref,
                    $txn->external_reference ?? '',
                    $txn->description ?? '',
                    number_format($txn->amount, 2, '.', ''),
                    ucfirst($txn->status),
                    $txn->mpesa_receipt ?? '',
                    $txn->payment_phone ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildPaymentsQuery(Request $request)
    {
        $query = WalletTransaction::with('client')
            ->orderByDesc('created_at');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->dateRange($request->from_date, $request->to_date);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_ref', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('mpesa_receipt', 'like', "%{$search}%")
                    ->orWhere('payment_phone', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('sender_id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
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

        $query = User::where('role', 'admin')
            ->where('client_id', 1); // Admin users belong to client_id 1

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = ListFilters::paginate(
            $query->orderBy('created_at', 'desc'),
            $request,
            25
        );

        return view('admin.admins.index', compact('admins'));
    }

    public function exportSenders(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $query = Client::query()->withCount(['campaigns', 'messages', 'contacts']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('sender_id', 'like', "%{$search}%")
                    ->orWhere('api_key', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        return CsvExporter::download(
            'senders_' . date('Y-m-d') . '.csv',
            ['ID', 'Name', 'Sender ID', 'Contact', 'Balance', 'Price/Unit', 'Status', 'Messages', 'Campaigns', 'Contacts', 'Created At'],
            function () use ($rows) {
                foreach ($rows as $client) {
                    yield [
                        $client->id,
                        $client->name,
                        $client->sender_id,
                        $client->contact,
                        number_format($client->balance ?? 0, 2, '.', ''),
                        number_format($client->price_per_unit ?? 0, 2, '.', ''),
                        $client->status ? 'Active' : 'Inactive',
                        $client->messages_count ?? 0,
                        $client->campaigns_count ?? 0,
                        $client->contacts_count ?? 0,
                        $client->created_at,
                    ];
                }
            }
        );
    }

    public function exportClientMessages(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        Client::findOrFail($id);

        $messagesQuery = Message::where('client_id', $id);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $messagesQuery->where(function ($q) use ($search) {
                $q->where('recipient', 'like', $search)
                    ->orWhere('body', 'like', $search)
                    ->orWhere('sender', 'like', $search);
            });
        }

        if ($request->filled('channel')) {
            $messagesQuery->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $messagesQuery->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $messagesQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $messagesQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $rows = $messagesQuery->orderByDesc('created_at')->limit(10000)->get();

        return CsvExporter::download(
            'sender_messages_' . $id . '_' . date('Y-m-d') . '.csv',
            ['Date', 'Recipient', 'Sender', 'Channel', 'Status', 'Cost (KES)', 'Body'],
            function () use ($rows) {
                foreach ($rows as $message) {
                    yield [
                        $message->created_at,
                        $message->recipient,
                        $message->sender,
                        $message->channel,
                        $message->status,
                        number_format($message->cost ?? 0, 2, '.', ''),
                        $message->body,
                    ];
                }
            }
        );
    }

    public function exportAdmins(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $query = User::where('role', 'admin')->where('client_id', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        return CsvExporter::download(
            'admins_' . date('Y-m-d') . '.csv',
            ['ID', 'Name', 'Email', 'Created At'],
            function () use ($rows) {
                foreach ($rows as $admin) {
                    yield [
                        $admin->id,
                        $admin->name,
                        $admin->email,
                        $admin->created_at,
                    ];
                }
            }
        );
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

