<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiDocumentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show API documentation page
     */
    public function index()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('dashboard')->with('error', 'No client associated with your account');
        }

        $baseUrl = url('/api');
        
        // API Endpoints documentation
        $endpoints = $this->getEndpoints($client->id);
        
        return view('api.documentation', compact('client', 'baseUrl', 'endpoints'));
    }

    /**
     * Get all API endpoints with documentation
     */
    private function getEndpoints($clientId)
    {
        return [
            [
                'category' => 'Messaging',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => "/{$clientId}/messages/send",
                        'name' => 'Send Message',
                        'description' => 'Send SMS, WhatsApp, or Email message',
                        'parameters' => [
                            ['name' => 'channel', 'type' => 'string', 'required' => true, 'description' => 'Message channel: sms, whatsapp, or email'],
                            ['name' => 'recipient', 'type' => 'string', 'required' => true, 'description' => 'Recipient phone/email (254XXXXXXXXX format for SMS)'],
                            ['name' => 'body', 'type' => 'string', 'required' => true, 'description' => 'Message content'],
                            ['name' => 'sender', 'type' => 'string', 'required' => false, 'description' => 'Sender ID'],
                            ['name' => 'subject', 'type' => 'string', 'required' => false, 'description' => 'Email subject (for email channel)'],
                        ],
                        'example' => [
                            'channel' => 'sms',
                            'recipient' => '254712345678',
                            'body' => 'Hello! This is a test message.',
                            'sender' => 'YOURCOMPANY'
                        ]
                    ],
                    [
                        'method' => 'POST',
                        'path' => "/{$clientId}/sms/send",
                        'name' => 'Send SMS',
                        'description' => 'Send SMS message (legacy endpoint)',
                        'parameters' => [
                            ['name' => 'recipients', 'type' => 'array', 'required' => true, 'description' => 'Array of phone numbers'],
                            ['name' => 'message', 'type' => 'string', 'required' => true, 'description' => 'SMS content'],
                            ['name' => 'sender_id', 'type' => 'string', 'required' => false, 'description' => 'Sender ID'],
                        ],
                        'example' => [
                            'recipients' => ['254712345678', '254700000000'],
                            'message' => 'Hello from our API!',
                            'sender_id' => 'YOURCOMPANY'
                        ]
                    ],
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/sms/history",
                        'name' => 'Get Message History',
                        'description' => 'Retrieve sent messages history',
                        'parameters' => [
                            ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Page number (default: 1)'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Items per page (default: 50)'],
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter by status'],
                        ],
                        'example' => null
                    ],
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/sms/status/{id}",
                        'name' => 'Check Message Status',
                        'description' => 'Get delivery status of a specific message',
                        'parameters' => [
                            ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'Message ID'],
                        ],
                        'example' => null
                    ],
                ]
            ],
            [
                'category' => 'Contacts',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/contacts",
                        'name' => 'List Contacts',
                        'description' => 'Get all contacts',
                        'parameters' => [
                            ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Page number'],
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Search by name or phone'],
                            ['name' => 'department', 'type' => 'string', 'required' => false, 'description' => 'Filter by department'],
                        ],
                        'example' => null
                    ],
                    [
                        'method' => 'POST',
                        'path' => "/{$clientId}/contacts",
                        'name' => 'Create Contact',
                        'description' => 'Add a new contact',
                        'parameters' => [
                            ['name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Contact name'],
                            ['name' => 'contact', 'type' => 'string', 'required' => true, 'description' => 'Phone number (254XXXXXXXXX)'],
                            ['name' => 'department', 'type' => 'string', 'required' => false, 'description' => 'Department/group'],
                        ],
                        'example' => [
                            'name' => 'John Doe',
                            'contact' => '254712345678',
                            'department' => 'Sales'
                        ]
                    ],
                    [
                        'method' => 'POST',
                        'path' => "/{$clientId}/contacts/bulk-import",
                        'name' => 'Bulk Import Contacts',
                        'description' => 'Import multiple contacts at once',
                        'parameters' => [
                            ['name' => 'contacts', 'type' => 'array', 'required' => true, 'description' => 'Array of contact objects'],
                        ],
                        'example' => [
                            'contacts' => [
                                ['name' => 'John Doe', 'contact' => '254712345678'],
                                ['name' => 'Jane Smith', 'contact' => '254700000000'],
                            ]
                        ]
                    ],
                ]
            ],
            [
                'category' => 'Campaigns',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/campaigns",
                        'name' => 'List Campaigns',
                        'description' => 'Get all campaigns',
                        'parameters' => [
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter by status'],
                        ],
                        'example' => null
                    ],
                    [
                        'method' => 'POST',
                        'path' => "/{$clientId}/campaigns",
                        'name' => 'Create Campaign',
                        'description' => 'Create a new campaign',
                        'parameters' => [
                            ['name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Campaign name'],
                            ['name' => 'message', 'type' => 'string', 'required' => true, 'description' => 'Message content'],
                            ['name' => 'recipients', 'type' => 'array', 'required' => true, 'description' => 'Array of phone numbers'],
                        ],
                        'example' => [
                            'name' => 'End of Month Sale',
                            'message' => 'Big sale this weekend! Visit us.',
                            'recipients' => ['254712345678', '254700000000']
                        ]
                    ],
                ]
            ],
            [
                'category' => 'Account',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/client/balance",
                        'name' => 'Check Balance',
                        'description' => 'Get current account balance',
                        'parameters' => [],
                        'example' => null
                    ],
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/client/statistics",
                        'name' => 'Get Statistics',
                        'description' => 'Get account statistics',
                        'parameters' => [
                            ['name' => 'from_date', 'type' => 'date', 'required' => false, 'description' => 'Start date'],
                            ['name' => 'to_date', 'type' => 'date', 'required' => false, 'description' => 'End date'],
                        ],
                        'example' => null
                    ],
                ]
            ],
            [
                'category' => 'Wallet',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/wallet/balance",
                        'name' => 'Get Wallet Balance',
                        'description' => 'Get wallet balance and units',
                        'parameters' => [],
                        'example' => null
                    ],
                    [
                        'method' => 'GET',
                        'path' => "/{$clientId}/wallet/transactions",
                        'name' => 'Get Transactions',
                        'description' => 'Get wallet transaction history',
                        'parameters' => [
                            ['name' => 'from_date', 'type' => 'date', 'required' => false, 'description' => 'Start date'],
                            ['name' => 'to_date', 'type' => 'date', 'required' => false, 'description' => 'End date'],
                        ],
                        'example' => null
                    ],
                    [
                        'method' => 'POST',
                        'path' => "/{$clientId}/wallet/topup",
                        'name' => 'Initiate Top-up',
                        'description' => 'Initiate M-Pesa top-up',
                        'parameters' => [
                            ['name' => 'amount', 'type' => 'number', 'required' => true, 'description' => 'Amount in KES (min: 100)'],
                            ['name' => 'payment_method', 'type' => 'string', 'required' => true, 'description' => 'mpesa or manual'],
                            ['name' => 'phone_number', 'type' => 'string', 'required' => true, 'description' => 'M-Pesa phone (254XXXXXXXXX)'],
                        ],
                        'example' => [
                            'amount' => 1000,
                            'payment_method' => 'mpesa',
                            'phone_number' => '254712345678'
                        ]
                    ],
                ]
            ],
        ];
    }
}

