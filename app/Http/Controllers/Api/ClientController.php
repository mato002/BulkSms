<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Sms;
use App\Models\Contact;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Get client profile
     */
    public function profile(Request $request): JsonResponse
    {
        $client = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $client->id,
                'name' => $client->name,
                'contact' => $client->contact,
                'sender_id' => $client->sender_id,
                'balance' => $client->balance,
                'status' => $client->status,
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at
            ]
        ]);
    }

    /**
     * Get client balance
     */
    public function balance(Request $request): JsonResponse
    {
        $client = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'balance' => $client->balance,
                'currency' => 'KES'
            ]
        ]);
    }

    /**
     * Get client statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $client = $request->user();
        $fromDate = $request->get('from_date', now()->subDays(30));
        $toDate = $request->get('to_date', now());

        $stats = [
            'total_contacts' => Contact::where('client_id', $client->id)->count(),
            'total_sms' => Sms::where('client_id', $client->id)
                            ->whereBetween('created_at', [$fromDate, $toDate])
                            ->count(),
            'sent_sms' => Sms::where('client_id', $client->id)
                            ->where('status', 'sent')
                            ->whereBetween('created_at', [$fromDate, $toDate])
                            ->count(),
            'delivered_sms' => Sms::where('client_id', $client->id)
                                ->where('status', 'delivered')
                                ->whereBetween('created_at', [$fromDate, $toDate])
                                ->count(),
            'failed_sms' => Sms::where('client_id', $client->id)
                              ->where('status', 'failed')
                              ->whereBetween('created_at', [$fromDate, $toDate])
                              ->count(),
            'total_campaigns' => Campaign::where('client_id', $client->id)
                                        ->whereBetween('created_at', [$fromDate, $toDate])
                                        ->count(),
            'total_cost' => Sms::where('client_id', $client->id)
                              ->whereBetween('created_at', [$fromDate, $toDate])
                              ->sum('cost')
        ];

        $stats['delivery_rate'] = $stats['total_sms'] > 0 ? 
            round(($stats['delivered_sms'] / $stats['total_sms']) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Update client profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $client = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'contact' => 'sometimes|string|max:255',
            'sender_id' => 'sometimes|string|max:11'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client->update($request->only(['name', 'contact', 'sender_id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $client
        ]);
    }

    /**
     * Admin: Get all clients
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $status = $request->get('status');

        $query = Client::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $clients = $query->orderBy('created_at', 'desc')
                        ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $clients
        ]);
    }

    /**
     * Admin: Create new client
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'sender_id' => 'required|string|max:11|unique:clients',
            'balance' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = Client::create([
            'name' => $request->name,
            'contact' => $request->contact,
            'sender_id' => $request->sender_id,
            'balance' => $request->balance ?? 0,
            'api_key' => $this->generateApiKey()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully',
            'data' => $client
        ], 201);
    }

    /**
     * Admin: Update client
     */
    public function update(Request $request, $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'contact' => 'sometimes|string|max:255',
            'sender_id' => 'sometimes|string|max:11|unique:clients,sender_id,' . $id,
            'balance' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client->update($request->only(['name', 'contact', 'sender_id', 'balance', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully',
            'data' => $client
        ]);
    }

    /**
     * Admin: Delete client
     */
    public function destroy($id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        $client->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * Admin: Get system statistics
     */
    public function adminStatistics(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', now()->subDays(30));
        $toDate = $request->get('to_date', now());

        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', true)->count(),
            'total_contacts' => Contact::count(),
            'total_sms' => Sms::whereBetween('created_at', [$fromDate, $toDate])->count(),
            'total_campaigns' => Campaign::whereBetween('created_at', [$fromDate, $toDate])->count(),
            'total_revenue' => Sms::whereBetween('created_at', [$fromDate, $toDate])->sum('cost'),
            'top_clients' => Client::withCount('sms')
                                 ->orderBy('sms_count', 'desc')
                                 ->limit(10)
                                 ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Generate unique API key
     */
    private function generateApiKey(): string
    {
        do {
            $apiKey = 'bs_' . bin2hex(random_bytes(16));
        } while (Client::where('api_key', $apiKey)->exists());

        return $apiKey;
    }
}
