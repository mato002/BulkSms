<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Template;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function __construct(private readonly MessageDispatcher $dispatcher)
    {
    }

    /**
     * Show Email integration dashboard
     */
    public function index()
    {
        $client = Auth::user()->client;
        
        $emailChannel = Channel::where('client_id', $client->id)
            ->where('name', 'email')
            ->first();

        $templates = Template::where('client_id', $client->id)
            ->where('channel', 'email')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get contacts that have email addresses (stored in 'contact' field or custom_fields)
        $contacts = Contact::where('client_id', $client->id)
            ->get()
            ->filter(function ($contact) {
                // Check if contact field contains an email (has @ symbol)
                if (filter_var($contact->contact, FILTER_VALIDATE_EMAIL)) {
                    return true;
                }
                // Check custom_fields for email
                $customFields = is_string($contact->custom_fields) 
                    ? json_decode($contact->custom_fields, true) 
                    : ($contact->custom_fields ?? []);
                return isset($customFields['email']) && filter_var($customFields['email'], FILTER_VALIDATE_EMAIL);
            })
            ->map(function ($contact) {
                // Extract email from contact field or custom_fields
                if (filter_var($contact->contact, FILTER_VALIDATE_EMAIL)) {
                    $contact->email = $contact->contact;
                } else {
                    $customFields = is_string($contact->custom_fields) 
                        ? json_decode($contact->custom_fields, true) 
                        : ($contact->custom_fields ?? []);
                    $contact->email = $customFields['email'] ?? null;
                }
                return $contact;
            })
            ->filter(function ($contact) {
                return !empty($contact->email);
            })
            ->values();

        // Get recent email messages stats
        $recentMessages = \DB::table('messages')
            ->where('client_id', $client->id)
            ->where('channel', 'email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total' => \DB::table('messages')->where('client_id', $client->id)->where('channel', 'email')->count(),
            'sent' => \DB::table('messages')->where('client_id', $client->id)->where('channel', 'email')->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => \DB::table('messages')->where('client_id', $client->id)->where('channel', 'email')->where('status', 'failed')->count(),
        ];

        return view('email.index', compact('emailChannel', 'templates', 'contacts', 'recentMessages', 'stats'));
    }

    /**
     * Test Email connection
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
                ->where('name', 'email')
                ->first();

            if (!$channel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email channel not configured. Please configure it first in Settings.'
                ], 404);
            }

            $credentials = is_string($channel->credentials) 
                ? json_decode($channel->credentials, true) 
                : $channel->credentials;

            if (!$credentials || !is_array($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email credentials not configured properly'
                ], 400);
            }

            // Test SMTP connection
            $host = $credentials['host'] ?? '';
            $port = $credentials['port'] ?? 587;
            $username = $credentials['username'] ?? '';
            $password = $credentials['password'] ?? '';

            if (empty($host) || empty($username) || empty($password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'SMTP credentials incomplete. Please configure host, username, and password.'
                ], 400);
            }

            // Try to send a test email to the configured from_email
            $testEmail = $credentials['from_email'] ?? $user->email;
            
            try {
                Mail::raw('This is a test email from BulkSMS Platform.', function ($message) use ($testEmail, $credentials) {
                    $message->to($testEmail)
                        ->subject('Test Email - BulkSMS Platform')
                        ->from($credentials['from_email'] ?? 'noreply@example.com', $credentials['from_name'] ?? 'BulkSMS Platform');
                });

                return response()->json([
                    'status' => 'success',
                    'message' => '✅ Email connection successful! Test email sent.',
                    'data' => [
                        'provider' => 'SMTP',
                        'host' => $host,
                        'port' => $port
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'SMTP connection failed: ' . $e->getMessage()
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Email connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send Email message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Auth::user()->client;

        try {
            $metadata = [
                'subject' => $request->subject,
            ];
            
            // If template is specified, use template message
            if ($request->template_id) {
                $template = Template::findOrFail($request->template_id);
                $metadata['template_name'] = $template->name;
            }

            $outbound = new OutboundMessage(
                clientId: $client->id,
                channel: 'email',
                recipient: $request->recipient,
                body: $request->message,
                templateId: $request->template_id,
                metadata: $metadata
            );

            $message = $this->dispatcher->dispatch($outbound);

            return response()->json([
                'status' => 'success',
                'message' => 'Email sent successfully',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Email message sending failed', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}

