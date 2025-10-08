<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\OnfonWalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOnfonBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onfon:sync-balances {--client=* : Specific client IDs to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync balances from Onfon Media for all clients with auto-sync enabled';

    /**
     * Execute the console command.
     */
    public function handle(OnfonWalletService $walletService): int
    {
        $this->info('Starting Onfon balance synchronization...');

        // Get clients to sync
        $clientIds = $this->option('client');
        
        if (!empty($clientIds)) {
            $clients = Client::whereIn('id', $clientIds)->where('status', true)->get();
        } else {
            // Sync only clients with auto_sync_balance enabled
            $clients = Client::where('auto_sync_balance', true)
                ->where('status', true)
                ->get();
        }

        if ($clients->isEmpty()) {
            $this->warn('No clients found for synchronization.');
            return self::SUCCESS;
        }

        $this->info("Found {$clients->count()} client(s) to sync.");

        $successCount = 0;
        $failCount = 0;

        foreach ($clients as $client) {
            $this->line("Syncing balance for: {$client->name} (ID: {$client->id})...");

            try {
                $result = $walletService->syncBalance($client);

                if ($result['success']) {
                    $this->info("  ✓ Success! Old: KES {$result['old_balance']} → New: KES {$result['new_balance']}");
                    $successCount++;
                    
                    // Update last sync time
                    $client->onfon_last_sync = now();
                    $client->onfon_balance = $result['new_balance'];
                    $client->save();
                } else {
                    $this->error("  ✗ Failed: " . ($result['message'] ?? 'Unknown error'));
                    $failCount++;
                    
                    Log::warning('Onfon balance sync failed', [
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                        'error' => $result['message'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Exception: " . $e->getMessage());
                $failCount++;
                
                Log::error('Onfon balance sync exception', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->newLine();
        $this->info("Synchronization complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Failed', $failCount],
                ['Total', $clients->count()]
            ]
        );

        return self::SUCCESS;
    }
}

