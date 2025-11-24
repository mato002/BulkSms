<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Template;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    public function __construct(private readonly MessageDispatcher $dispatcher)
    {
    }

    /**
     * Show SMS integration dashboard
     */
    public function index()
    {
        $client = Auth::user()->client;
        
        $smsChannel = Channel::where('client_id', $client->id)
            ->where('name', 'sms')
            ->first();

        $templates = Template::where('client_id', $client->id)
            ->where('channel', 'sms')
            ->orderBy('created_at', 'desc')
            ->get();

        $contacts = Contact::where('client_id', $client->id)
            ->orderBy('name', 'asc')
            ->get();

        // Get recent SMS messages stats
        $recentMessages = \DB::table('messages')
            ->where('client_id', $client->id)
            ->where('channel', 'sms')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total' => \DB::table('messages')->where('client_id', $client->id)->where('channel', 'sms')->count(),
            'sent' => \DB::table('messages')->where('client_id', $client->id)->where('channel', 'sms')->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => \DB::table('messages')->where('client_id', $client->id)->where('channel', 'sms')->where('status', 'failed')->count(),
        ];

        return view('sms.index', compact('smsChannel', 'templates', 'contacts', 'recentMessages', 'stats'));
    }

    /**
     * Test SMS connection
     */
    public function testConnection(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->client_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No client associated with this user'
                ], 400);
            }
            
            $client = $user->client;
            
            if (!$client) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client not found'
                ], 404);
            }
            
            $channel = Channel::where('client_id', $client->id)
                ->where('name', 'sms')
                ->first();

            if (!$channel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'SMS channel not configured. Please configure it first in Settings.'
                ], 404);
            }

            $credentials = is_string($channel->credentials) 
                ? json_decode($channel->credentials, true) 
                : $channel->credentials;

            if (!$credentials || !is_array($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'SMS credentials not configured properly'
                ], 400);
            }

            // Test Onfon connection by checking balance
            $apiKey = $credentials['api_key'] ?? '';
            $clientId = $credentials['client_id'] ?? '';
            $accessKeyHeader = $credentials['access_key_header'] ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB';

            if (empty($apiKey) || empty($clientId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API Key or Client ID missing'
                ], 400);
            }

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'AccessKey' => $accessKeyHeader,
                ])
                ->get('https://api.onfonmedia.co.ke/v1/sms/Balance', [
                    'ApiKey' => $apiKey,
                    'ClientId' => $clientId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $balance = $data['Data'][0]['Credits'] ?? 0;
                
                return response()->json([
                    'status' => 'success',
                    'message' => '✅ SMS connection successful!',
                    'data' => [
                        'balance' => $balance,
                        'provider' => 'Onfon Media'
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . ($response->json()['ErrorMessage'] ?? 'Invalid credentials')
            ], 400);

        } catch (\Exception $e) {
            Log::error('SMS connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string',
            'message' => 'required|string|max:1600',
            'sender_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Auth::user()->client;

        try {
            $outbound = new OutboundMessage(
                clientId: $client->id,
                channel: 'sms',
                recipient: $request->recipient,
                body: $request->message,
                sender: $request->sender_id ?? $client->sender_id ?? null
            );

            $message = $this->dispatcher->dispatch($outbound);

            return response()->json([
                'status' => 'success',
                'message' => 'SMS sent successfully',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('SMS message sending failed', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }
}

