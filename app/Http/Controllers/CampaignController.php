<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Campaign;
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $clientId = session('client_id', 1);
        
        $query = Campaign::where('client_id', $clientId);
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sender_id', 'like', "%{$search}%");
            });
        }
        
        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $campaigns = $query->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $clientId = session('client_id', 1);
        
        // Get client details including sender_id
        $client = DB::table('clients')->where('id', $clientId)->first();
        
        // Get all available senders/clients
        $senders = DB::table('clients')
            ->where('status', 1) // Only active clients
            ->orderBy('name')
            ->get(['id', 'name', 'sender_id', 'company_name']);
        
        $contacts = DB::table('contacts')
            ->where('client_id', $clientId)
            ->orderBy('name')
            ->get();
        
        // Get unique departments for filtering
        $departments = DB::table('contacts')
            ->where('client_id', $clientId)
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();
        
        return view('campaigns.create', compact('contacts', 'departments', 'client', 'senders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,whatsapp',
            'message' => 'required|string',
            'sender_id' => 'nullable|string|max:255',
            'template_id' => 'nullable|exists:templates,id',
            'recipients' => 'required|string',
        ]);

        $clientId = session('client_id', 1);
        $recipients = array_filter(array_map('trim', explode(',', $validated['recipients'])));

        Campaign::create([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'message' => $validated['message'],
            'sender_id' => $validated['sender_id'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
            'recipients' => $recipients,
            'status' => 'draft',
            'total_recipients' => count($recipients),
        ]);

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully');
    }

    public function show(string $id)
    {
        $clientId = session('client_id', 1);
        $campaign = Campaign::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            abort(404);
        }

        return view('campaigns.show', compact('campaign'));
    }

    public function edit(string $id)
    {
        $clientId = session('client_id', 1);
        
        // Get client details including sender_id
        $client = DB::table('clients')->where('id', $clientId)->first();
        
        // Get all available senders/clients
        $senders = DB::table('clients')
            ->where('status', 1) // Only active clients
            ->orderBy('name')
            ->get(['id', 'name', 'sender_id', 'company_name']);
        
        $campaign = Campaign::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            abort(404);
        }

        return view('campaigns.edit', compact('campaign', 'client', 'senders'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,whatsapp',
            'message' => 'required|string',
            'sender_id' => 'nullable|string|max:255',
            'template_id' => 'nullable|exists:templates,id',
        ]);

        $clientId = session('client_id', 1);
        
        $campaign = Campaign::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            abort(404);
        }

        $campaign->update([
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'message' => $validated['message'],
            'sender_id' => $validated['sender_id'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
        ]);

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully');
    }

    public function destroy(string $id)
    {
        $clientId = session('client_id', 1);

        $campaign = Campaign::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if ($campaign) {
            $campaign->delete();
        }

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully');
    }

    public function send(string $id, MessageDispatcher $dispatcher)
    {
        $clientId = session('client_id', 1);
        $campaign = Campaign::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            abort(404);
        }

        $recipients = $campaign->recipients ?? [];
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                $outbound = new OutboundMessage(
                    clientId: $clientId,
                    channel: $campaign->channel ?? 'sms', // Use campaign's channel
                    recipient: $recipient,
                    sender: $campaign->sender_id,
                    body: $campaign->message,
                    templateId: $campaign->template_id ?? null
                );

                $dispatcher->dispatch($outbound);
                $sent++;
            } catch (\Throwable $e) {
                \Log::error('Campaign message failed', [
                    'campaign_id' => $id,
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);

        return redirect()->route('campaigns.show', $id)->with('success', "Campaign sent! {$sent} succeeded, {$failed} failed.");
    }

    /**
     * Send a scheduled campaign (called by job)
     */
    public function sendScheduledCampaign(Campaign $campaign)
    {
        $dispatcher = app(MessageDispatcher::class);
        
        $recipients = $campaign->recipients ?? [];
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                $outbound = new OutboundMessage(
                    clientId: $campaign->client_id,
                    channel: $campaign->channel ?? 'sms',
                    recipient: $recipient,
                    sender: $campaign->sender_id,
                    body: $campaign->message,
                    templateId: $campaign->template_id ?? null
                );

                $dispatcher->dispatch($outbound);
                $sent++;
            } catch (\Throwable $e) {
                \Log::error('Scheduled campaign message failed', [
                    'campaign_id' => $campaign->id,
                    'recipient' => $recipient,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);

        \Log::info('Scheduled campaign sent', [
            'campaign_id' => $campaign->id,
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return ['sent' => $sent, 'failed' => $failed];
    }
}
