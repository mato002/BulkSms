<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Template;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    public function __construct(private readonly MessageDispatcher $dispatcher)
    {
    }

    /**
     * Show WhatsApp integration dashboard
     */
    public function index()
    {
        $client = Auth::user()->client;
        
        $whatsappChannel = Channel::where('client_id', $client->id)
            ->where('name', 'whatsapp')
            ->first();

        $templates = Template::where('client_id', $client->id)
            ->where('channel', 'whatsapp')
            ->orderBy('created_at', 'desc')
            ->get();

        $contacts = Contact::where('client_id', $client->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('whatsapp.index', compact('whatsappChannel', 'templates', 'contacts'));
    }

    /**
     * Show configuration form
     */
    public function configure()
    {
        $client = Auth::user()->client;
        
        $whatsappChannel = Channel::where('client_id', $client->id)
            ->where('name', 'whatsapp')
            ->first();

        return view('whatsapp.configure', compact('whatsappChannel'));
    }

    /**
     * Save WhatsApp configuration
     */
    public function saveConfiguration(Request $request)
    {
        $provider = $request->provider ?? 'ultramsg';

        if ($provider === 'ultramsg') {
            $validator = Validator::make($request->all(), [
                'instance_id' => 'required|string',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $client = Auth::user()->client;

            $credentials = [
                'instance_id' => $request->instance_id,
                'token' => $request->token,
            ];

            // Create or update WhatsApp channel
            $channel = Channel::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'name' => 'whatsapp',
                ],
                [
                    'provider' => 'ultramsg',
                    'credentials' => json_encode($credentials),
                    'active' => true,
                    'config' => json_encode([
                        'webhook_token' => $request->webhook_token ?? \Illuminate\Support\Str::random(32),
                    ]),
                ]
            );

            return redirect()->route('whatsapp.index')
                ->with('success', 'UltraMsg WhatsApp configuration saved successfully! 🎉');
        }

        // Fallback to Cloud API configuration
        $validator = Validator::make($request->all(), [
            'phone_number_id' => 'required|string',
            'access_token' => 'required|string',
            'business_account_id' => 'nullable|string',
            'webhook_verify_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $client = Auth::user()->client;

        $credentials = [
            'phone_number_id' => $request->phone_number_id,
            'access_token' => $request->access_token,
            'api_version' => $request->api_version ?? 'v21.0',
        ];

        if ($request->business_account_id) {
            $credentials['business_account_id'] = $request->business_account_id;
        }

        // Create or update WhatsApp channel
        $channel = Channel::updateOrCreate(
            [
                'client_id' => $client->id,
                'name' => 'whatsapp',
            ],
            [
                'provider' => 'whatsapp_cloud',
                'credentials' => json_encode($credentials),
                'active' => true,
                'config' => json_encode([
                    'webhook_verify_token' => $request->webhook_verify_token ?? \Illuminate\Support\Str::random(32),
                ]),
            ]
        );

        return redirect()->route('whatsapp.index')
            ->with('success', 'WhatsApp configuration saved successfully!');
    }

    /**
     * Test WhatsApp connection
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
                ->where('name', 'whatsapp')
                ->first();

            if (!$channel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'WhatsApp channel not configured. Please configure it first.'
                ], 404);
            }

            $credentials = is_string($channel->credentials) 
                ? json_decode($channel->credentials, true) 
                : $channel->credentials;

            if (!$credentials || !is_array($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'WhatsApp credentials not configured properly'
                ], 400);
            }
            
            if ($channel->provider === 'ultramsg') {
                // Test UltraMsg connection
                $instanceId = $credentials['instance_id'];
                $token = $credentials['token'];

                $response = Http::timeout(30)
                    ->withOptions(['verify' => false]) // Disable SSL verification for development
                    ->get("https://api.ultramsg.com/{$instanceId}/instance/status", [
                        'token' => $token
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return response()->json([
                        'status' => 'success',
                        'message' => '✅ UltraMsg connection successful!',
                        'data' => $data
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Connection failed: ' . ($response->json()['error'] ?? 'Invalid credentials')
                ], 400);
            }

            // Cloud API connection test
            $phoneNumberId = $credentials['phone_number_id'];
            $accessToken = $credentials['access_token'];
            $apiVersion = $credentials['api_version'] ?? 'v21.0';

            $response = Http::withToken($accessToken)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->get("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}");

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Connection successful!',
                    'data' => $data
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . ($response->json()['error']['message'] ?? 'Unknown error')
            ], 400);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'WhatsApp channel not found. Please configure it first.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('WhatsApp connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string',
            'message' => 'required|string',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Auth::user()->client;

        try {
            $metadata = [];
            
            // If template is specified, use template message
            if ($request->template_id) {
                $template = Template::findOrFail($request->template_id);
                $metadata = [
                    'template_name' => $template->name,
                    'language_code' => $template->language ?? 'en',
                ];

                // Parse template variables if any
                if ($request->variables) {
                    $metadata['template_components'] = [
                        [
                            'type' => 'body',
                            'parameters' => array_map(function($var) {
                                return ['type' => 'text', 'text' => $var];
                            }, $request->variables)
                        ]
                    ];
                }
            }

            $outbound = new OutboundMessage(
                clientId: $client->id,
                channel: 'whatsapp',
                recipient: $request->recipient,
                body: $request->message,
                templateId: $request->template_id,
                metadata: $metadata
            );

            $message = $this->dispatcher->dispatch($outbound);

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp message sending failed', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send interactive message (buttons)
     */
    public function sendInteractive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string',
            'type' => 'required|in:button,list',
            'header' => 'nullable|string',
            'body' => 'required|string',
            'footer' => 'nullable|string',
            'buttons' => 'required_if:type,button|array',
            'sections' => 'required_if:type,list|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Auth::user()->client;

        try {
            $metadata = [
                'interactive_type' => $request->type,
            ];

            if ($request->header) {
                $metadata['header'] = $request->header;
            }

            if ($request->footer) {
                $metadata['footer'] = $request->footer;
            }

            if ($request->type === 'button') {
                $metadata['buttons'] = $request->buttons;
            } else {
                $metadata['sections'] = $request->sections;
                $metadata['action_button'] = $request->action_button ?? 'Select';
            }

            $outbound = new OutboundMessage(
                clientId: $client->id,
                channel: 'whatsapp',
                recipient: $request->recipient,
                body: $request->body,
                metadata: $metadata
            );

            $message = $this->dispatcher->dispatch($outbound);

            return response()->json([
                'status' => 'success',
                'message' => 'Interactive message sent successfully',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload media to WhatsApp
     */
    public function uploadMedia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:16384', // 16MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Auth::user()->client;
        
        $channel = Channel::where('client_id', $client->id)
            ->where('name', 'whatsapp')
            ->firstOrFail();

        $credentials = is_string($channel->credentials) 
            ? json_decode($channel->credentials, true) 
            : $channel->credentials;
        $phoneNumberId = $credentials['phone_number_id'];
        $accessToken = $credentials['access_token'];
        $apiVersion = $credentials['api_version'] ?? 'v21.0';

        try {
            $file = $request->file('file');
            
            $response = Http::withToken($accessToken)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->attach('file', file_get_contents($file), $file->getClientOriginalName())
                ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/media", [
                    'messaging_product' => 'whatsapp',
                    'type' => $file->getMimeType(),
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json([
                    'status' => 'success',
                    'media_id' => $result['id'],
                    'message' => 'Media uploaded successfully'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: ' . ($response->json()['error']['message'] ?? 'Unknown error')
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch WhatsApp templates from API
     */
    public function fetchTemplates()
    {
        $client = Auth::user()->client;
        
        $channel = Channel::where('client_id', $client->id)
            ->where('name', 'whatsapp')
            ->firstOrFail();

        // Check if using UltraMsg (doesn't support template fetching)
        if ($channel->provider === 'ultramsg') {
            return response()->json([
                'status' => 'error',
                'message' => 'Template syncing is only available for WhatsApp Cloud API. UltraMsg does not support this feature.'
            ], 400);
        }

        $credentials = is_string($channel->credentials) 
            ? json_decode($channel->credentials, true) 
            : $channel->credentials;
        
        $businessAccountId = $credentials['business_account_id'] ?? null;
        $accessToken = $credentials['access_token'] ?? null;
        $apiVersion = $credentials['api_version'] ?? 'v21.0';

        if (!$accessToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access token not configured'
            ], 400);
        }

        if (!$businessAccountId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business Account ID not configured'
            ], 400);
        }

        try {
            $response = Http::withToken($accessToken)
                ->withOptions(['verify' => false]) // Disable SSL verification for development
                ->get("https://graph.facebook.com/{$apiVersion}/{$businessAccountId}/message_templates");

            if ($response->successful()) {
                $templates = $response->json()['data'] ?? [];
                
                // Sync templates to database
                foreach ($templates as $templateData) {
                    if ($templateData['status'] === 'APPROVED') {
                        Template::updateOrCreate(
                            [
                                'client_id' => $client->id,
                                'channel' => 'whatsapp',
                                'name' => $templateData['name'],
                            ],
                            [
                                'language' => $templateData['language'],
                                'category' => $templateData['category'] ?? 'MARKETING',
                                'status' => strtolower($templateData['status']),
                                'components' => json_encode($templateData['components'] ?? []),
                                'metadata' => json_encode($templateData),
                            ]
                        );
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => count($templates) . ' templates synced successfully',
                    'templates' => $templates
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch templates: ' . ($response->json()['error']['message'] ?? 'Unknown error')
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch templates: ' . $e->getMessage()
            ], 500);
        }
    }
}

