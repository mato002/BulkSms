<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\Template;
use App\Models\Conversation;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Client;
use App\Models\Tag;
use App\Models\ApiLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Get the authenticated user's client ID.
     */
    private function getClientId()
    {
        $user = Auth::user();
        return $user && $user->client_id ? $user->client_id : 1;
    }

    /**
     * Check if user is admin.
     */
    private function isAdmin()
    {
        $user = Auth::user();
        return $user && ($user->role === 'admin' || $user->client_id === 1);
    }

    /**
     * Perform a global search across the application.
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ], 400);
            }

            $clientId = $this->getClientId();
            $results = [];

            // Search Contacts
            try {
                $contacts = Contact::where('client_id', $clientId)
                    ->where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('contact', 'like', "%{$query}%")
                          ->orWhere('department', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'name', 'contact', 'department']);

                if ($contacts->isNotEmpty()) {
                    $results['contacts'] = $contacts->map(function($contact) {
                        return [
                            'id' => $contact->id,
                            'title' => $contact->name,
                            'subtitle' => $contact->contact,
                            'type' => 'contact',
                            'url' => route('contacts.show', $contact->id),
                            'icon' => 'bi-person-circle'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Contact search failed: ' . $e->getMessage());
            }

            // Search Messages
            try {
                $messages = Message::where('client_id', $clientId)
                    ->where(function($q) use ($query) {
                        $q->where('content', 'like', "%{$query}%")
                          ->orWhere('to', 'like', "%{$query}%")
                          ->orWhere('from', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'content', 'to', 'channel', 'created_at']);

                if ($messages->isNotEmpty()) {
                    $results['messages'] = $messages->map(function($message) {
                        return [
                            'id' => $message->id,
                            'title' => 'Message to ' . $message->to,
                            'subtitle' => substr($message->content, 0, 50) . '...',
                            'type' => 'message',
                            'url' => route('messages.show', $message->id),
                            'icon' => 'bi-chat-dots'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Message search failed: ' . $e->getMessage());
            }

            // Search Campaigns
            try {
                $campaigns = Campaign::where('client_id', $clientId)
                    ->where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('message', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'name', 'status', 'message']);

                if ($campaigns->isNotEmpty()) {
                    $results['campaigns'] = $campaigns->map(function($campaign) {
                        return [
                            'id' => $campaign->id,
                            'title' => $campaign->name,
                            'subtitle' => 'Status: ' . ucfirst($campaign->status),
                            'type' => 'campaign',
                            'url' => route('campaigns.show', $campaign->id),
                            'icon' => 'bi-megaphone'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Campaign search failed: ' . $e->getMessage());
            }

            // Search Templates
            try {
                $templates = Template::where('client_id', $clientId)
                    ->where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('content', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'name', 'content', 'channel']);

                if ($templates->isNotEmpty()) {
                    $results['templates'] = $templates->map(function($template) {
                        return [
                            'id' => $template->id,
                            'title' => $template->name,
                            'subtitle' => ucfirst($template->channel) . ' Template',
                            'type' => 'template',
                            'url' => route('templates.edit', $template->id),
                            'icon' => 'bi-file-text'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Template search failed: ' . $e->getMessage());
            }

            // Search Conversations
            try {
                $conversations = Conversation::where('client_id', $clientId)
                    ->where(function($q) use ($query) {
                        $q->where('contact_name', 'like', "%{$query}%")
                          ->orWhere('contact_phone', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'contact_name', 'contact_phone', 'last_message_preview']);

                if ($conversations->isNotEmpty()) {
                    $results['conversations'] = $conversations->map(function($conversation) {
                        return [
                            'id' => $conversation->id,
                            'title' => $conversation->contact_name,
                            'subtitle' => $conversation->contact_phone,
                            'type' => 'conversation',
                            'url' => route('inbox.show', $conversation->id),
                            'icon' => 'bi-chat-left-text'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Conversation search failed: ' . $e->getMessage());
            }

            // Search Wallet Transactions
            try {
                $walletTransactions = WalletTransaction::where('client_id', $clientId)
                    ->where(function($q) use ($query) {
                        $q->where('transaction_ref', 'like', "%{$query}%")
                          ->orWhere('description', 'like', "%{$query}%")
                          ->orWhere('payment_phone', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'transaction_ref', 'amount', 'type', 'status', 'description']);

                if ($walletTransactions->isNotEmpty()) {
                    $results['transactions'] = $walletTransactions->map(function($transaction) {
                        return [
                            'id' => $transaction->id,
                            'title' => $transaction->transaction_ref,
                            'subtitle' => ucfirst($transaction->type) . ' - KSh ' . number_format($transaction->amount, 2) . ' (' . ucfirst($transaction->status) . ')',
                            'type' => 'transaction',
                            'url' => route('wallet.index'),
                            'icon' => 'bi-wallet2'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Transaction search failed: ' . $e->getMessage());
            }

            // Search Tags
            try {
                $tags = Tag::where('client_id', $clientId)
                    ->where('name', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'name', 'color']);

                if ($tags->isNotEmpty()) {
                    $results['tags'] = $tags->map(function($tag) {
                        return [
                            'id' => $tag->id,
                            'title' => $tag->name,
                            'subtitle' => 'Tag',
                            'type' => 'tag',
                            'url' => route('contacts.index', ['tag' => $tag->id]),
                            'icon' => 'bi-tag'
                        ];
                    })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Tag search failed: ' . $e->getMessage());
            }

            // Admin-only searches
            if ($this->isAdmin()) {
                // Search Users
                try {
                    $users = User::where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'role']);

                    if ($users->isNotEmpty()) {
                        $results['users'] = $users->map(function($user) {
                            $url = $user->role === 'admin' 
                                ? route('admin.admins.edit', $user->id)
                                : route('admin.clients.show', $user->client_id);
                            return [
                                'id' => $user->id,
                                'title' => $user->name,
                                'subtitle' => $user->email . ' (' . ucfirst($user->role) . ')',
                                'type' => 'user',
                                'url' => $url,
                                'icon' => 'bi-person-badge'
                            ];
                        })->toArray();
                    }
                } catch (\Exception $e) {
                    Log::warning('User search failed: ' . $e->getMessage());
                }

                // Search Clients
                try {
                    $clients = Client::where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%")
                          ->orWhere('phone', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'phone']);

                    if ($clients->isNotEmpty()) {
                        $results['clients'] = $clients->map(function($client) {
                            return [
                                'id' => $client->id,
                                'title' => $client->name,
                                'subtitle' => $client->email ?? $client->phone,
                                'type' => 'client',
                                'url' => route('admin.clients.show', $client->id),
                                'icon' => 'bi-building'
                            ];
                        })->toArray();
                    }
                } catch (\Exception $e) {
                    Log::warning('Client search failed: ' . $e->getMessage());
                }

                // Search API Logs
                try {
                    $apiLogs = ApiLog::where(function($q) use ($query) {
                        $q->where('endpoint', 'like', "%{$query}%")
                          ->orWhere('method', 'like', "%{$query}%")
                          ->orWhere('ip_address', 'like', "%{$query}%");
                    })
                    ->limit(5)
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'endpoint', 'method', 'ip_address', 'success']);

                    if ($apiLogs->isNotEmpty()) {
                        $results['api_logs'] = $apiLogs->map(function($log) {
                            return [
                                'id' => $log->id,
                                'title' => $log->method . ' ' . $log->endpoint,
                                'subtitle' => $log->ip_address . ' - ' . ($log->success ? 'Success' : 'Failed'),
                                'type' => 'api_log',
                                'url' => route('api-monitor.index', ['endpoint' => $log->endpoint]),
                                'icon' => 'bi-code-square'
                            ];
                        })->toArray();
                    }
                } catch (\Exception $e) {
                    Log::warning('API log search failed: ' . $e->getMessage());
                }
            }

            // Search Pages/Navigation (always available) - Do this FIRST so pages appear at top
            try {
                $pages = $this->searchPages($query);
                if (!empty($pages)) {
                    $results['pages'] = $pages;
                }
            } catch (\Exception $e) {
                Log::warning('Page search failed: ' . $e->getMessage());
            }

            // Flatten results for easier rendering - Put pages first
            $flatResults = [];
            
            // Add pages first if they exist
            if (isset($results['pages']) && !empty($results['pages'])) {
                foreach ($results['pages'] as $page) {
                    $flatResults[] = $page;
                }
            }
            
            // Then add other results
            foreach ($results as $category => $items) {
                if ($category !== 'pages') {
                    foreach ($items as $item) {
                        $flatResults[] = $item;
                    }
                }
            }

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $flatResults,
            'total' => count($flatResults),
            'categories' => $results
        ]);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage(), [
                'query' => $request->input('q'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search for pages/navigation items.
     */
    private function searchPages($query)
    {
        $queryLower = strtolower($query);
        $pages = [];
        $user = Auth::user();
        $isAdmin = $this->isAdmin();
        $isTenant = $user && $user->client_id && $user->client_id !== 1;

        // Define all searchable pages with safe route handling (each route handled individually)
        $allPages = [];
        
        // Helper function to safely add a page
        $addPage = function($title, $keywords, $routeName, $icon) use (&$allPages) {
            try {
                $allPages[] = [
                    'title' => $title,
                    'keywords' => $keywords,
                    'url' => route($routeName),
                    'icon' => $icon
                ];
            } catch (\Exception $e) {
                // Skip this route if it doesn't exist
            }
        };
        
        // Add all pages
        $addPage('Wallet', ['wallet', 'balance', 'topup', 'top-up', 'credit', 'fund'], 'wallet.index', 'bi-wallet2');
        $addPage('Top Up Balance', ['topup', 'top-up', 'add funds', 'deposit', 'wallet'], 'wallet.topup', 'bi-plus-circle');
        $addPage('Settings', ['settings', 'config', 'configuration', 'preferences'], 'settings.index', 'bi-gear');
        $addPage('Dashboard', ['dashboard', 'home', 'main'], 'dashboard', 'bi-speedometer2');
        $addPage('Contacts', ['contacts', 'contact', 'people'], 'contacts.index', 'bi-person-lines-fill');
        $addPage('Messages', ['messages', 'message', 'sms'], 'messages.index', 'bi-chat-dots');
        $addPage('Campaigns', ['campaigns', 'campaign', 'bulk'], 'campaigns.index', 'bi-megaphone');
        $addPage('Templates', ['templates', 'template'], 'templates.index', 'bi-file-text');
        $addPage('Inbox', ['inbox', 'conversations', 'chat'], 'inbox.index', 'bi-inbox');
        $addPage('Analytics', ['analytics', 'reports', 'statistics', 'stats'], 'analytics.index', 'bi-graph-up');
        $addPage('Profile', ['profile', 'account', 'user', 'my profile'], 'profile.show', 'bi-person');
        $addPage('Notifications', ['notifications', 'notification', 'alerts'], 'notifications.index', 'bi-bell');
        $addPage('WhatsApp', ['whatsapp', 'whats app', 'wa'], 'whatsapp.index', 'bi-whatsapp');
        $addPage('WhatsApp Configuration', ['whatsapp config', 'whatsapp setup', 'configure whatsapp'], 'whatsapp.configure', 'bi-gear');
        $addPage('Tags', ['tags', 'tag', 'label', 'labels'], 'tags.index', 'bi-tag');
        $addPage('API Documentation', ['api docs', 'api documentation', 'developer', 'api'], 'api.docs', 'bi-code-square');

        // Tenant-specific pages
        if ($isTenant) {
            $addPage('Tenant Dashboard', ['tenant dashboard', 'my dashboard'], 'tenant.dashboard', 'bi-speedometer2');
            $addPage('Tenant Profile', ['tenant profile', 'my profile'], 'tenant.profile', 'bi-person');
            $addPage('Tenant API Docs', ['tenant api', 'api docs'], 'tenant.api-docs', 'bi-code-square');
            $addPage('Tenant Notifications', ['tenant notifications'], 'tenant.notifications', 'bi-bell');
        }

        // Admin-only pages
        if ($isAdmin) {
            $addPage('Manage Clients', ['clients', 'client', 'tenants'], 'admin.clients.index', 'bi-building');
            $addPage('Manage Admins', ['admins', 'admin', 'users'], 'admin.admins.index', 'bi-person-badge');
            $addPage('API Monitor', ['api', 'monitor', 'logs', 'api logs'], 'api-monitor.index', 'bi-code-square');
            $addPage('API Statistics', ['api stats', 'statistics', 'api statistics'], 'api-monitor.statistics', 'bi-graph-up-arrow');
            $addPage('Manage Senders', ['senders', 'sender', 'phone numbers'], 'admin.senders.index', 'bi-telephone');
        }

        // Search through pages
        foreach ($allPages as $page) {
            $titleLower = strtolower($page['title']);
            $keywordsMatch = false;
            
            // Check if query matches title
            if (str_contains($titleLower, $queryLower)) {
                $pages[] = [
                    'id' => 'page-' . md5($page['url']),
                    'title' => $page['title'],
                    'subtitle' => 'Page',
                    'type' => 'page',
                    'url' => $page['url'],
                    'icon' => $page['icon']
                ];
                continue;
            }

            // Check if query matches any keyword
            foreach ($page['keywords'] as $keyword) {
                if (str_contains($keyword, $queryLower) || str_contains($queryLower, $keyword)) {
                    $keywordsMatch = true;
                    break;
                }
            }

            if ($keywordsMatch) {
                $pages[] = [
                    'id' => 'page-' . md5($page['url']),
                    'title' => $page['title'],
                    'subtitle' => 'Page',
                    'type' => 'page',
                    'url' => $page['url'],
                    'icon' => $page['icon']
                ];
            }
        }

        return $pages;
    }

    /**
     * Show search results page.
     */
    public function showResults(Request $request)
    {
        $query = $request->input('q', '');
        $clientId = $this->getClientId();

        if (strlen($query) < 2) {
            return redirect()->route('dashboard')->with('error', 'Search query must be at least 2 characters');
        }

        // Get all results (without limits for the results page)
        $contacts = Contact::where('client_id', $clientId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('contact', 'like', "%{$query}%")
                  ->orWhere('department', 'like', "%{$query}%");
            })
            ->paginate(10, ['*'], 'contacts_page');

        $messages = Message::where('client_id', $clientId)
            ->where(function($q) use ($query) {
                $q->where('content', 'like', "%{$query}%")
                  ->orWhere('to', 'like', "%{$query}%");
            })
            ->paginate(10, ['*'], 'messages_page');

        $campaigns = Campaign::where('client_id', $clientId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('message', 'like', "%{$query}%");
            })
            ->paginate(10, ['*'], 'campaigns_page');

        $templates = Template::where('client_id', $clientId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->paginate(10, ['*'], 'templates_page');

        $conversations = Conversation::where('client_id', $clientId)
            ->where(function($q) use ($query) {
                $q->where('contact_name', 'like', "%{$query}%")
                  ->orWhere('contact_phone', 'like', "%{$query}%");
            })
            ->paginate(10, ['*'], 'conversations_page');

        return view('search.results', compact('query', 'contacts', 'messages', 'campaigns', 'templates', 'conversations'));
    }
}


