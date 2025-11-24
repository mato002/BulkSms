<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;

class CredentialEncryptionService
{
    public function __construct(private readonly Encrypter $encrypter)
    {
    }

    /**
     * Encrypt credentials array
     */
    public function encryptCredentials(array $credentials): string
    {
        try {
            $jsonCredentials = json_encode($credentials);
            return $this->encrypter->encrypt($jsonCredentials);
        } catch (\Exception $e) {
            Log::error('Failed to encrypt credentials', [
                'error' => $e->getMessage(),
                'credentials_keys' => array_keys($credentials)
            ]);
            throw new \Exception('Failed to encrypt credentials: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt credentials string
     */
    public function decryptCredentials(string $encryptedCredentials): array
    {
        try {
            $jsonCredentials = $this->encrypter->decrypt($encryptedCredentials);
            return json_decode($jsonCredentials, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to decrypt credentials', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Check if credentials are encrypted
     */
    public function isEncrypted(string $credentials): bool
    {
        try {
            // Try to decrypt - if it fails, it's not encrypted
            $this->encrypter->decrypt($credentials);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Safely get credentials (handles both encrypted and plain text)
     */
    public function getCredentials(string $credentials): array
    {
        if ($this->isEncrypted($credentials)) {
            return $this->decryptCredentials($credentials);
        }

        // Try to decode as JSON (legacy plain text)
        $decoded = json_decode($credentials, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Migrate plain text credentials to encrypted
     */
    public function migrateCredentials(string $plainCredentials): string
    {
        $decoded = json_decode($plainCredentials, true);
        if (is_array($decoded)) {
            return $this->encryptCredentials($decoded);
        }
        return $plainCredentials;
    }
}



