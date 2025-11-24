<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\AdminSetting;
use App\Models\AlertPhoneNumber;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $clientId = session('client_id', 1);
        
        // Get client info
        $client = DB::table('clients')->where('id', $clientId)->first();
        
        // Get all channels for this client
        $channels = DB::table('channels')->where('client_id', $clientId)->get();
        
        // Define all available channel types
        $availableChannels = [
            'sms' => [
                'name' => 'SMS',
                'provider' => 'onfon',
                'icon' => 'phone',
                'description' => 'SMS messaging via Onfon Media'
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'provider' => 'ultramsg',
                'icon' => 'whatsapp',
                'description' => 'WhatsApp messaging via UltraMsg or WhatsApp Cloud API'
            ],
            'email' => [
                'name' => 'Email',
                'provider' => 'smtp',
                'icon' => 'envelope',
                'description' => 'Email messaging via SMTP'
            ]
        ];
        
        // Create a map of existing channels by name
        $existingChannelsMap = $channels->keyBy('name');
        
        // Build channels list - ensure all channel types are shown
        $channelsWithCreds = collect($availableChannels)->map(function ($channelInfo, $channelName) use ($existingChannelsMap, $client) {
            $existingChannel = $existingChannelsMap->get($channelName);
            
            if ($existingChannel) {
                // Channel exists - decode credentials
                $creds = json_decode($existingChannel->credentials ?? '{}', true);
                $existingChannel->credentials_array = $creds;
                $existingChannel->channel_info = $channelInfo;
                return $existingChannel;
            } else {
                // Channel doesn't exist - create a placeholder object
                return (object) [
                    'id' => null,
                    'name' => $channelName,
                    'provider' => $channelInfo['provider'],
                    'active' => false,
                    'credentials' => null,
                    'credentials_array' => [],
                    'channel_info' => $channelInfo,
                    'exists' => false
                ];
            }
        })->values();

        // Attempt to read cached Onfon balance (with graceful fallback if Redis extension is missing)
        $onfonBalance = 0.0;
        $onfonBalanceLastRefreshed = 'Never';
        $onfonCacheAvailable = true;

        try {
            $onfonBalance = (float) Cache::get('onfon_system_balance', 0);
            $onfonBalanceLastRefreshed = Cache::has('onfon_system_balance') ? 'Recently' : 'Never';
        } catch (\Throwable $exception) {
            $onfonCacheAvailable = false;

            Log::warning('Unable to read Onfon balance from cache', [
                'client_id' => $clientId,
                'message' => $exception->getMessage(),
            ]);
        }

        // Get admin settings if user is admin
        $adminSettings = null;
        $phoneNumbers = null;
        if (Auth::user()->isAdmin()) {
            $adminSettings = AdminSetting::all();
            $phoneNumbers = AlertPhoneNumber::orderBy('created_at', 'desc')->get();
        }

        $selectedChannel = request()->query('channel');

        return view('settings.index', compact(
            'client',
            'channelsWithCreds',
            'adminSettings',
            'phoneNumbers',
            'onfonBalance',
            'onfonBalanceLastRefreshed',
            'onfonCacheAvailable',
            'selectedChannel'
        ));
    }

    public function updateClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'sender_id' => 'required|string|max:255',
        ]);

        $clientId = session('client_id', 1);

        DB::table('clients')
            ->where('id', $clientId)
            ->update([
                'name' => $validated['name'],
                'contact' => $validated['contact'],
                'sender_id' => $validated['sender_id'],
                'updated_at' => now(),
            ]);

        return redirect()->route('settings.index')->with('success', 'Client information updated successfully');
    }

    public function updateChannel(Request $request, $channelId)
    {
        $clientId = session('client_id', 1);
        
        $channel = DB::table('channels')
            ->where('id', $channelId)
            ->where('client_id', $clientId)
            ->first();

        if (!$channel) {
            abort(404);
        }

        $validated = $request->validate([
            'active' => 'required|boolean',
            'api_key' => 'nullable|string',
            'client_id_value' => 'nullable|string',
            'access_key_header' => 'nullable|string',
            'default_sender' => 'nullable|string',
            // SMTP/Email fields
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
        ]);

        // Build credentials array based on provider
        $credentials = [];
        
        if ($channel->provider === 'onfon') {
            // Onfon SMS credentials
            if ($request->filled('api_key')) {
                $credentials['api_key'] = $validated['api_key'];
            }
            if ($request->filled('client_id_value')) {
                $credentials['client_id'] = $validated['client_id_value'];
            }
            if ($request->filled('access_key_header')) {
                $credentials['access_key_header'] = $validated['access_key_header'];
            }
            if ($request->filled('default_sender')) {
                $credentials['default_sender'] = $validated['default_sender'];
            }
        } elseif ($channel->provider === 'smtp') {
            // SMTP Email credentials
            $existingCreds = json_decode($channel->credentials ?? '{}', true);
            $credentials = $existingCreds; // Preserve existing values
            
            if ($request->filled('smtp_host')) {
                $credentials['host'] = $validated['smtp_host'];
            }
            if ($request->filled('smtp_port')) {
                $credentials['port'] = $validated['smtp_port'];
            }
            if ($request->filled('smtp_username')) {
                $credentials['username'] = $validated['smtp_username'];
            }
            if ($request->filled('smtp_password')) {
                $credentials['password'] = $validated['smtp_password'];
            }
            if ($request->filled('smtp_encryption')) {
                $credentials['encryption'] = $validated['smtp_encryption'];
            }
            if ($request->filled('from_email')) {
                $credentials['from_email'] = $validated['from_email'];
            }
            if ($request->filled('from_name')) {
                $credentials['from_name'] = $validated['from_name'];
            }
        }

        DB::table('channels')
            ->where('id', $channelId)
            ->update([
                'active' => $validated['active'],
                'credentials' => json_encode($credentials),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('settings.index', ['channel' => $channel->name])
            ->with('success', 'Channel updated successfully');
    }

    public function regenerateApiKey()
    {
        $clientId = session('client_id', 1);
        $newApiKey = Str::uuid()->toString();

        DB::table('clients')
            ->where('id', $clientId)
            ->update([
                'api_key' => $newApiKey,
                'updated_at' => now(),
            ]);

        return redirect()->route('settings.index')->with('success', 'API key regenerated successfully');
    }

    // Admin Settings Methods
    public function updateAdminSettings(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'low_balance_threshold' => 'required|numeric|min:1',
            'admin_phone' => 'nullable|string|min:10',
            'balance_refresh_interval' => 'required|numeric|min:1|max:1440',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        AdminSetting::set('low_balance_threshold', $request->low_balance_threshold, 'number', 'Minimum Onfon balance units before sending low balance alert');
        AdminSetting::set('admin_phone', $request->admin_phone, 'string', 'Phone number to receive low balance SMS alerts');
        AdminSetting::set('balance_refresh_interval', $request->balance_refresh_interval, 'number', 'How often to refresh Onfon balance (in minutes)');

        return back()->with('success', 'Admin settings updated successfully!');
    }

    public function addPhoneNumber(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:10|max:15',
            'name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        AlertPhoneNumber::create([
            'phone_number' => $request->phone_number,
            'name' => $request->name,
            'notes' => $request->notes,
            'is_active' => true
        ]);

        return back()->with('success', 'Phone number added successfully!');
    }

    public function togglePhoneNumber($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $phoneNumber = AlertPhoneNumber::findOrFail($id);
        $phoneNumber->is_active = !$phoneNumber->is_active;
        $phoneNumber->save();

        $status = $phoneNumber->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "Phone number {$status} successfully!");
    }

    public function deletePhoneNumber($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $phoneNumber = AlertPhoneNumber::findOrFail($id);
        $phoneNumber->delete();

        return back()->with('success', 'Phone number deleted successfully!');
    }

    public function createChannel(Request $request)
    {
        $clientId = session('client_id', 1);
        
        $validated = $request->validate([
            'name' => 'required|in:sms,whatsapp,email',
            'provider' => 'required|string',
        ]);

        // Check if channel already exists
        $existing = DB::table('channels')
            ->where('client_id', $clientId)
            ->where('name', $validated['name'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Channel already exists!');
        }

        // Get client info for defaults
        $client = DB::table('clients')->where('id', $clientId)->first();

        // Default credentials based on channel type
        $credentials = [];
        if ($validated['name'] === 'sms') {
            $onfonConfig = config('sms.gateways.onfon', []);
            $credentials = [
                'api_key' => $onfonConfig['api_key'] ?? '',
                'client_id' => $onfonConfig['client_id'] ?? '',
                'access_key_header' => env('ONFON_ACCESS_KEY_HEADER', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB'),
                'default_sender' => $client->sender_id ?? 'DEFAULT',
            ];
        } elseif ($validated['name'] === 'email') {
            $credentials = [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_email' => $client->contact ?? 'noreply@example.com',
                'from_name' => $client->name ?? 'BulkSMS Platform'
            ];
        } elseif ($validated['name'] === 'whatsapp') {
            $credentials = [
                'instance_id' => '',
                'token' => '',
            ];
        }

        DB::table('channels')->insert([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'credentials' => json_encode($credentials),
            'active' => false, // Inactive by default until configured
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('settings.index', ['channel' => $validated['name']])
            ->with('success', ucfirst($validated['name']) . ' channel created successfully! Please configure it below.');
    }
}
