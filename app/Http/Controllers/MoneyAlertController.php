<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoneyAlertController extends Controller
{
    public function unreadCount()
    {
        $user = Auth::user();
        $query = $this->transactionsQuery($user);
        $this->applyUnreadFilter($query, $user);

        return response()->json(['count' => $query->count()]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $lastSeen = $this->lastSeenAt($user);
        $limit = min((int) $request->get('limit', 15), 30);

        $alerts = $this->transactionsQuery($user)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (WalletTransaction $transaction) => $this->formatAlert($transaction, $lastSeen, $user));

        return response()->json(['alerts' => $alerts]);
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs['money_alerts_last_seen_at'] = now()->toIso8601String();
        $user->preferences = $prefs;
        $user->save();

        return response()->json(['success' => true]);
    }

    private function transactionsQuery(User $user)
    {
        $query = WalletTransaction::query()->with('client:id,sender_id,name');

        if ($user->isAdmin()) {
            return $query;
        }

        $clientId = $user->client_id ?? session('client_id', 1);

        return $query->where('client_id', $clientId);
    }

    private function applyUnreadFilter($query, User $user): void
    {
        $lastSeen = $this->lastSeenAt($user);

        if ($lastSeen) {
            $query->whereRaw('COALESCE(completed_at, updated_at, created_at) > ?', [$lastSeen]);

            return;
        }

        $query->where(function ($inner) {
            $inner->whereIn('status', ['pending', 'processing'])
                ->orWhere('created_at', '>=', now()->subHours(48));
        });
    }

    private function lastSeenAt(User $user): ?Carbon
    {
        $value = $user->preferences['money_alerts_last_seen_at'] ?? null;

        return $value ? Carbon::parse($value) : null;
    }

    private function activityAt(WalletTransaction $transaction): Carbon
    {
        return $transaction->completed_at
            ?? $transaction->updated_at
            ?? $transaction->created_at;
    }

    private function formatAlert(WalletTransaction $transaction, ?Carbon $lastSeen, User $user): array
    {
        $activityAt = $this->activityAt($transaction);
        $isCredit = $transaction->type === 'credit';
        $amount = (float) $transaction->amount;

        if ($isCredit && $transaction->status === 'completed') {
            $title = 'Payment received';
            $color = 'success';
            $icon = 'bi-arrow-down-circle';
            $sign = '+';
        } elseif ($isCredit && in_array($transaction->status, ['pending', 'processing'], true)) {
            $title = 'Pending top-up';
            $color = 'warning';
            $icon = 'bi-hourglass-split';
            $sign = '';
        } elseif ($isCredit && $transaction->status === 'failed') {
            $title = 'Payment failed';
            $color = 'danger';
            $icon = 'bi-x-circle';
            $sign = '';
        } else {
            $title = 'Balance used';
            $color = 'primary';
            $icon = 'bi-arrow-up-circle';
            $sign = '-';
        }

        $sender = $transaction->client?->sender_id ?? 'Unknown';
        $method = $transaction->payment_method_label;
        $subtitle = $user->isAdmin()
            ? $sender . ' · ' . $method
            : $method . ($transaction->description ? ' · ' . $transaction->description : '');

        $url = $user->isAdmin()
            ? route('admin.payments.index', ['search' => $transaction->transaction_ref])
            : route('wallet.index');

        return [
            'id' => $transaction->id,
            'title' => $title,
            'subtitle' => $subtitle,
            'amount_label' => $sign . 'KES ' . number_format($amount, 2),
            'status' => $transaction->status,
            'type' => $transaction->type,
            'color' => $color,
            'icon' => $icon,
            'unread' => !$lastSeen || $activityAt->gt($lastSeen),
            'time' => $activityAt->diffForHumans(),
            'url' => $url,
        ];
    }
}
