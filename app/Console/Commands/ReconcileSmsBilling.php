<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Message;
use App\Services\SmsSegmenter;
use App\Services\WalletCreditService;
use Illuminate\Console\Command;

class ReconcileSmsBilling extends Command
{
    protected $signature = 'billing:reconcile-sms
                            {--client= : Limit to a specific client ID}
                            {--dry-run : Show what would be charged without debiting}
                            {--allow-negative : Allow balance to go below zero for historical usage}';

    protected $description = 'Bill sent SMS messages that were not debited from client wallets';

    public function handle(WalletCreditService $wallet): int
    {
        $clientId = $this->option('client');
        $dryRun = (bool) $this->option('dry-run');
        $allowNegative = (bool) $this->option('allow-negative');

        $query = Message::query()
            ->where('channel', 'sms')
            ->whereIn('status', ['sent', 'delivered'])
            ->orderBy('id');

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $messages = $query->get();
        $billed = 0;
        $skipped = 0;
        $totalCharged = 0.0;

        $this->info('Checking ' . $messages->count() . ' sent SMS message(s)...');

        foreach ($messages as $message) {
            if ($wallet->hasDebitForMessage($message->client_id, $message->id)) {
                $skipped++;
                continue;
            }

            $client = Client::find($message->client_id);
            if (!$client) {
                $this->warn("Skipping message #{$message->id}: client not found");
                $skipped++;
                continue;
            }

            $cost = (float) $message->cost;
            if ($cost <= 0) {
                $unitPrice = (float) ($client->price_per_unit ?? config('sms.default_cost', 0.75));
                $segments = SmsSegmenter::segmentInfo($message->body ?? '')['segments'];
                $cost = round(max($unitPrice, 0.01) * $segments, 4);

                if (!$dryRun) {
                    $message->cost = $cost;
                    $message->save();
                }
            }

            if ($cost <= 0) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    'Would bill client #%d message #%d: KES %s',
                    $client->id,
                    $message->id,
                    number_format($cost, 2)
                ));
                $billed++;
                $totalCharged += $cost;
                continue;
            }

            try {
                $wallet->debit(
                    $client,
                    $cost,
                    sprintf('SMS reconcile #%d to %s', $message->id, $message->recipient),
                    [
                        'message_id' => $message->id,
                        'channel' => 'sms',
                        'recipient' => $message->recipient,
                        'reconciled' => true,
                    ],
                    $allowNegative
                );

                $billed++;
                $totalCharged += $cost;
            } catch (\Throwable $e) {
                $this->error("Failed message #{$message->id}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would bill' : 'Billed') . ": {$billed} message(s), KES " . number_format($totalCharged, 2));
        $this->info("Skipped (already billed or invalid): {$skipped}");

        return self::SUCCESS;
    }
}
