<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        
        // Decrypt credentials for display
        $channelsWithCreds = $channels->map(function ($channel) {
            $creds = json_decode($channel->credentials ?? '{}', true);
            $channel->credentials_array = $creds;
            return $channel;
        });

        // Get admin settings if user is admin
        $adminSettings = null;
        $phoneNumbers = null;
        if (Auth::user()->isAdmin()) {
            $adminSettings = AdminSetting::all();
            $phoneNumbers = AlertPhoneNumber::orderBy('created_at', 'desc')->get();
        }

        return view('settings.index', compact('client', 'channelsWithCreds', 'adminSettings', 'phoneNumbers'));
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
        ]);

        // Build credentials array
        $credentials = [];
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

        DB::table('channels')
            ->where('id', $channelId)
            ->update([
                'active' => $validated['active'],
                'credentials' => json_encode($credentials),
                'updated_at' => now(),
            ]);

        return redirect()->route('settings.index')->with('success', 'Channel updated successfully');
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
}
