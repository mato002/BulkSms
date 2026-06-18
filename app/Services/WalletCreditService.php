<?php

namespace App\Services;

use App\Models\Client;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletCreditService
{
    /**
     * Credit a client's wallet and record the transaction.
     *
     * @return array{transaction: WalletTransaction, old_balance: float, new_balance: float}
     */
    public function credit(
        Client $client,
        float $amount,
        string $paymentMethod = 'partner',
        ?string $reference = null,
        ?string $description = null,
        array $metadata = []
    ): array {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $reference = $reference ?: WalletTransaction::generateTransactionRef();

        return DB::transaction(function () use ($client, $amount, $paymentMethod, $reference, $description, $metadata) {
            $existing = WalletTransaction::where('transaction_ref', $reference)->first();
            if ($existing && $existing->status === 'completed') {
                $client->refresh();

                return [
                    'transaction' => $existing,
                    'old_balance' => (float) $client->balance,
                    'new_balance' => (float) $client->balance,
                    'duplicate' => true,
                ];
            }

            $lockedClient = Client::lockForUpdate()->findOrFail($client->id);
            $oldBalance = (float) $lockedClient->balance;

            $transaction = WalletTransaction::create([
                'client_id' => $lockedClient->id,
                'type' => 'credit',
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_ref' => $reference,
                'status' => 'completed',
                'completed_at' => now(),
                'description' => $description ?? 'Wallet credit',
                'metadata' => $metadata,
            ]);

            $lockedClient->addBalance($amount);

            return [
                'transaction' => $transaction,
                'old_balance' => $oldBalance,
                'new_balance' => (float) $lockedClient->fresh()->balance,
                'duplicate' => false,
            ];
        });
    }

    /**
     * Debit a client's wallet for SMS/message usage and record the transaction.
     *
     * @return array{transaction: WalletTransaction, old_balance: float, new_balance: float, duplicate?: bool}
     */
    public function debit(
        Client $client,
        float $amount,
        string $description,
        array $metadata = [],
        bool $allowNegative = false
    ): array {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Debit amount must be greater than zero.');
        }

        $messageId = $metadata['message_id'] ?? null;

        return DB::transaction(function () use ($client, $amount, $description, $metadata, $allowNegative, $messageId) {
            if ($messageId) {
                $existing = WalletTransaction::query()
                    ->where('client_id', $client->id)
                    ->where('type', 'debit')
                    ->where('status', 'completed')
                    ->where('metadata->message_id', $messageId)
                    ->first();

                if ($existing) {
                    $client->refresh();

                    return [
                        'transaction' => $existing,
                        'old_balance' => (float) $client->balance,
                        'new_balance' => (float) $client->balance,
                        'duplicate' => true,
                    ];
                }
            }

            $lockedClient = Client::lockForUpdate()->findOrFail($client->id);
            $oldBalance = (float) $lockedClient->balance;

            if (!$allowNegative && $oldBalance < $amount) {
                throw new \RuntimeException(
                    'Insufficient balance. Required: KES ' . number_format($amount, 2)
                    . ', available: KES ' . number_format($oldBalance, 2)
                );
            }

            $transaction = WalletTransaction::create([
                'client_id' => $lockedClient->id,
                'type' => 'debit',
                'amount' => $amount,
                'payment_method' => 'usage',
                'transaction_ref' => WalletTransaction::generateTransactionRef(),
                'status' => 'completed',
                'completed_at' => now(),
                'description' => $description,
                'metadata' => $metadata,
            ]);

            $newBalance = round($oldBalance - $amount, 2);
            $lockedClient->balance = $newBalance;
            $lockedClient->save();

            return [
                'transaction' => $transaction,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'duplicate' => false,
            ];
        });
    }

    public function hasDebitForMessage(int $clientId, int $messageId): bool
    {
        return WalletTransaction::query()
            ->where('client_id', $clientId)
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->where('metadata->message_id', $messageId)
            ->exists();
    }
}
