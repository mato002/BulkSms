<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\Template;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Perform a global search across the application.
     */
    public function search(Request $request)
    {
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
        $contacts = Contact::where('client_id', $clientId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('contact', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'contact', 'email']);

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

        // Search Messages
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

        // Search Campaigns
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

        // Search Templates
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

        // Search Conversations
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

        // Flatten results for easier rendering
        $flatResults = [];
        foreach ($results as $category => $items) {
            foreach ($items as $item) {
                $flatResults[] = $item;
            }
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $flatResults,
            'total' => count($flatResults),
            'categories' => $results
        ]);
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
                  ->orWhere('email', 'like', "%{$query}%");
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


