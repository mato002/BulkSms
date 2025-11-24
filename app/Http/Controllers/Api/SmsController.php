<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sms;
use App\Models\Client;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send SMS to multiple recipients
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recipients' => 'required|array|max:1000',
            'recipients.*' => 'required|string|regex:/^254[0-9]{9}$/',
            // Allow longer than 160; segmentation will compute parts & cost
            'message' => 'required|string|max:2000',
            'sender_id' => 'nullable|string|max:11',
            'schedule_time' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = $request->user();
        $results = [];
        $totalCost = 0;

        foreach ($request->recipients as $recipient) {
            $result = $this->smsService->sendSms(
                $client,
                $recipient,
                $request->message,
                $request->sender_id ?? $client->sender_id
            );
            
            $results[] = $result;
            $totalCost += $result['cost'] ?? 0;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'SMS sent successfully',
            'data' => [
                'results' => $results,
                'total_recipients' => count($request->recipients),
                'total_cost' => $totalCost
            ]
        ]);
    }

    /**
     * Get SMS status
     */
    public function status(Request $request, $id): JsonResponse
    {
        $client = $request->user();
        $sms = Sms::where('id', $id)
                  ->where('client_id', $client->id)
                  ->first();

        if (!$sms) {
            return response()->json([
                'status' => 'error',
                'message' => 'SMS not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $sms->id,
                'recipient' => $sms->recipient,
                'status' => $sms->status,
                'message_id' => $sms->message_id,
                'sent_at' => $sms->sent_at,
                'delivered_at' => $sms->delivered_at,
                'cost' => $sms->cost
            ]
        ]);
    }

    /**
     * Get SMS history
     */
    public function history(Request $request): JsonResponse
    {
        $client = $request->user();
        $perPage = $request->get('per_page', 50);
        $status = $request->get('status');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Sms::where('client_id', $client->id)
            ->with('client'); // Eager load client relationship

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $sms = $query->orderBy('created_at', 'desc')
                     ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $sms
        ]);
    }

    /**
     * Get SMS statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $client = $request->user();
        $fromDate = $request->get('from_date', now()->subDays(30));
        $toDate = $request->get('to_date', now());

        $stats = Sms::where('client_id', $client->id)
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->selectRaw('
                        COUNT(*) as total_sms,
                        SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
                        SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_count,
                        SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count,
                        SUM(cost) as total_cost
                    ')
                    ->first();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
