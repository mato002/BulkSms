<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\CredentialEncryptionService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $credentialService = app(CredentialEncryptionService::class);
        
        // Get all channels with credentials
        $channels = DB::table('channels')
            ->whereNotNull('credentials')
            ->where('credentials', '!=', '')
            ->get();
        
        foreach ($channels as $channel) {
            try {
                // Check if already encrypted
                if ($credentialService->isEncrypted($channel->credentials)) {
                    continue; // Skip already encrypted credentials
                }
                
                // Encrypt the credentials
                $encryptedCredentials = $credentialService->migrateCredentials($channel->credentials);
                
                // Update the channel with encrypted credentials
                DB::table('channels')
                    ->where('id', $channel->id)
                    ->update([
                        'credentials' => $encryptedCredentials,
                        'updated_at' => now()
                    ]);
                    
            } catch (\Exception $e) {
                // Log error but continue with other channels
                \Illuminate\Support\Facades\Log::error('Failed to encrypt credentials for channel ' . $channel->id, [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This is a one-way migration for security reasons
        // Decrypting credentials would be a security risk
        // If rollback is needed, restore from backup
        \Illuminate\Support\Facades\Log::warning('Credential encryption migration rollback attempted - this is not recommended for security reasons');
    }
};