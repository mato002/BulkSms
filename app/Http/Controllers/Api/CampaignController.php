<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Get all campaigns for the client
     */
    public function index(Request $request): JsonResponse
    {
        $client = $request->user();
        $perPage = $request->get('per_page', 50);
        $status = $request->get('status');

        $query = Campaign::where('client_id', $client->id)
            ->with('client'); // Eager load client relationship

        if ($status) {
            $query->where('status', $status);
        }

        $campaigns = $query->orderBy('created_at', 'desc')
                          ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $campaigns
        ]);
    }

    /**
     * Create a new campaign
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:160',
            'sender_id' => 'nullable|string|max:11',
            'recipients' => 'required|array|min:1|max:10000',
            'recipients.*' => 'required|string|regex:/^254[0-9]{9}$/',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = $request->user();
        
        $campaign = Campaign::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'message' => $request->message,
            'sender_id' => $request->sender_id ?? $client->sender_id,
            'recipients' => $request->recipients,
            'total_recipients' => count($request->recipients),
            'scheduled_at' => $request->scheduled_at,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign created successfully',
            'data' => $campaign
        ], 201);
    }

    /**
     * Update a campaign
     */
    public function update(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $campaign = Campaign::where('id', $id)
                           ->where('client_id', $client->id)
                           ->first();

        if (!$campaign) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign not found'
            ], 404);
        }

        if ($campaign->status === 'sent') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update sent campaign'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'message' => 'sometimes|string|max:160',
            'sender_id' => 'nullable|string|max:11',
            'recipients' => 'sometimes|array|min:1|max:10000',
            'recipients.*' => 'required|string|regex:/^254[0-9]{9}$/',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'message', 'sender_id', 'scheduled_at']);
        
        if ($request->has('recipients')) {
            $updateData['recipients'] = $request->recipients;
            $updateData['total_recipients'] = count($request->recipients);
        }

        $campaign->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign updated successfully',
            'data' => $campaign
        ]);
    }

    /**
     * Send a campaign
     */
    public function send(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $campaign = Campaign::where('id', $id)
                           ->where('client_id', $client->id)
                           ->first();

        if (!$campaign) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign not found'
            ], 404);
        }

        if ($campaign->status === 'sent') {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign already sent'
            ], 422);
        }

        // Check if client has sufficient balance
        $totalCost = count($campaign->recipients) * 0.75;
        if (!$client->hasSufficientBalance($totalCost)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance'
            ], 422);
        }

        // Send SMS to all recipients
        $results = [];
        foreach ($campaign->recipients as $recipient) {
            $result = $this->smsService->sendSms(
                $client,
                $recipient,
                $campaign->message,
                $campaign->sender_id
            );
            $results[] = $result;
        }

        // Update campaign status
        $campaign->markAsSent();
        $campaign->updateStats();

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign sent successfully',
            'data' => [
                'campaign_id' => $campaign->id,
                'total_recipients' => count($campaign->recipients),
                'results' => $results
            ]
        ]);
    }

    /**
     * Get campaign statistics
     */
    public function statistics(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $campaign = Campaign::where('id', $id)
                           ->where('client_id', $client->id)
                           ->first();

        if (!$campaign) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign not found'
            ], 404);
        }

        $campaign->updateStats();

        return response()->json([
            'status' => 'success',
            'data' => [
                'campaign_id' => $campaign->id,
                'name' => $campaign->name,
                'total_recipients' => $campaign->total_recipients,
                'sent_count' => $campaign->sent_count,
                'delivered_count' => $campaign->delivered_count,
                'failed_count' => $campaign->failed_count,
                'total_cost' => $campaign->total_cost,
                'delivery_rate' => $campaign->total_recipients > 0 ? 
                    round(($campaign->delivered_count / $campaign->total_recipients) * 100, 2) : 0
            ]
        ]);
    }

    /**
     * Delete a campaign
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $campaign = Campaign::where('id', $id)
                           ->where('client_id', $client->id)
                           ->first();

        if (!$campaign) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign not found'
            ], 404);
        }

        if ($campaign->status === 'sent') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete sent campaign'
            ], 422);
        }

        $campaign->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign deleted successfully'
        ]);
    }
}
