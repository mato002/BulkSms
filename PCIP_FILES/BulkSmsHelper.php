<?php
/**
 * BulkSms CRM Helper for PCIP
 * 
 * Integrates PCIP with BulkSms CRM using FORTRESS sender ID
 * 
 * @author PCIP Development Team
 * @version 1.0
 */

class BulkSmsHelper
{
    private $apiUrl;
    private $apiKey;
    private $clientId;
    private $senderId;
    private $balanceUrl;
    
    public function __construct()
    {
        // FORTRESS API Credentials
        $this->apiUrl = 'http://127.0.0.1:8000/api/2/messages/send';
        $this->apiKey = 'USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh';
        $this->clientId = 2;
        $this->senderId = 'FORTRESS';
        $this->balanceUrl = 'http://127.0.0.1:8000/api/2/client/balance';
    }
    
    /**
     * Send SMS message
     * 
     * @param string $recipient Phone number (accepts formats: 0728883160, 728883160, 254728883160)
     * @param string $message Message content (max 160 characters for single SMS)
     * @return array ['success' => bool, 'message_id' => int, 'status' => string, 'error' => string]
     */
    public function sendSms($recipient, $message)
    {
        // Format phone number
        $recipient = $this->formatPhoneNumber($recipient);
        
        // Validate message
        if (empty($message)) {
            return [
                'success' => false,
                'error' => 'Message cannot be empty'
            ];
        }
        
        // Prepare request data
        $data = [
            'client_id' => $this->clientId,
            'channel' => 'sms',
            'recipient' => $recipient,
            'sender' => $this->senderId,
            'body' => $message
        ];
        
        // Initialize cURL
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle connection errors
        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        // Parse response
        $responseData = json_decode($response, true);
        
        // Handle success
        if ($httpCode >= 200 && $httpCode < 300) {
            $status = $responseData['status'] ?? 'unknown';
            
            return [
                'success' => ($status === 'sent'),
                'message_id' => $responseData['id'] ?? null,
                'status' => $status,
                'provider_message_id' => $responseData['provider_message_id'] ?? null,
                'error' => ($status !== 'sent') ? 'Message failed to send' : null
            ];
        }
        
        // Handle API errors
        return [
            'success' => false,
            'error' => $responseData['message'] ?? 'Unknown error occurred',
            'errors' => $responseData['errors'] ?? [],
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Check FORTRESS account balance
     * 
     * @return array|null Balance information or null on error
     */
    public function checkBalance()
    {
        $ch = curl_init($this->balanceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Send SMS to multiple recipients
     * 
     * @param array $recipients Array of phone numbers
     * @param string $message Message content
     * @return array Results for each recipient
     */
    public function sendBulkSms($recipients, $message)
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $result = $this->sendSms($recipient, $message);
            $results[] = [
                'recipient' => $recipient,
                'success' => $result['success'],
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null
            ];
            
            // Small delay to avoid overwhelming the API
            usleep(100000); // 0.1 second
        }
        
        return $results;
    }
    
    /**
     * Format phone number to international format (254XXXXXXXXX)
     * 
     * @param string $phone Phone number in various formats
     * @return string Formatted phone number
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading + if present
        $phone = ltrim($phone, '+');
        
        // If starts with 0, replace with 254 (Kenya)
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // If doesn't start with 254, add it
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Validate Kenyan phone number
     * 
     * @param string $phone Phone number
     * @return bool True if valid
     */
    public function isValidPhoneNumber($phone)
    {
        $formatted = $this->formatPhoneNumber($phone);
        
        // Valid Kenyan number should be 254 followed by 9 digits
        // Starting with 7 or 1 (mobile) or 2 (landline)
        return preg_match('/^254[712]\d{8}$/', $formatted);
    }
}


