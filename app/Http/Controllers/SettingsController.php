<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        return view('settings.index', compact('client', 'channelsWithCreds'));
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
}
