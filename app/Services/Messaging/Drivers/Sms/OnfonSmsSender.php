<?php

namespace App\Services\Messaging\Drivers\Sms;

use App\Services\Messaging\Contracts\MessageSender;
use App\Services\Messaging\DTO\OutboundMessage;
use Illuminate\Support\Facades\Http;

class OnfonSmsSender implements MessageSender
{
    public function __construct(private readonly array $credentials)
    {
    }

    public function send(OutboundMessage $message): string
    {
        // Match working Api project's exact structure
        $apiKey = $this->credentials['api_key'] ?? '';
        $clientId = $this->credentials['client_id'] ?? '';
        $accessKeyHeader = $this->credentials['access_key_header'] ?? '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB';

        if ($apiKey === '' || $clientId === '') {
            throw new \RuntimeException('Onfon credentials missing: api_key/client_id');
        }

        $url = 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS';

        $payload = [
            'ApiKey' => $apiKey,
            'ClientId' => $clientId,
            'IsUnicode' => 1,
            'IsFlash' => 1,
            'SenderId' => $message->sender ?? ($this->credentials['default_sender'] ?? ''),
            'MessageParameters' => [
                [
                    'Number' => $message->recipient,
                    'Text' => $message->body,
                ],
            ],
        ];

        $caPath = $this->credentials['ca_bundle'] ?? 'C:\\cacert\\cacert.pem';

        $resp = Http::timeout(20)
            ->withOptions(['verify' => false]) // Disable SSL verification for development (overrides $caPath)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
                'AccessKey' => $accessKeyHeader,
            ])
            ->post($url, $payload);

        $status = $resp->status();
        $bodyText = $resp->body();
        $response = $resp->json() ?? [];

        // Clear balance cache after sending SMS to trigger immediate refresh
        if ($status >= 200 && $status < 300) {
            cache()->forget('onfon_system_balance');
        }

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException("Onfon HTTP {$status}: {$bodyText}");
        }

        $errorCode = $response['ErrorCode'] ?? null;
        if ($errorCode !== 0 && $errorCode !== '000') {
            $msg = $response['ErrorMessage'] ?? 'Unknown error';
            throw new \RuntimeException("Onfon ErrorCode {$errorCode}: {$msg}");
        }

        // Parse message IDs from Data array
        $data = $response['Data'] ?? [];
        if (empty($data)) {
            throw new \RuntimeException("Onfon success but no Data returned");
        }

        $first = $data[0] ?? [];
        $msgErrorCode = $first['MessageErrorCode'] ?? null;
        $providerId = $first['MessageId'] ?? ($first['messageId'] ?? null);

        if ($msgErrorCode !== 0 && $msgErrorCode !== '000') {
            $errMsg = $first['MessageErrorDescription'] ?? 'Message send failed';
            throw new \RuntimeException("Onfon MessageErrorCode {$msgErrorCode}: {$errMsg}");
        }

        if (empty($providerId)) {
            throw new \RuntimeException("Onfon success but no MessageId returned");
        }

        return (string) $providerId;
    }

    public function channel(): string
    {
        return 'sms';
    }

    public function provider(): string
    {
        return 'onfon';
    }
}


