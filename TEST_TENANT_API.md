# Testing Tenant API - Send SMS

## Quick Start

### 1. Test the API
```powershell
.\test-tenant-api.ps1 -ClientId <YOUR_CLIENT_ID> -ApiKey <YOUR_API_KEY> -Recipient "254712345678" -Message "Test message"
```

### 2. Watch Logs in Real-Time
```powershell
.\watch-logs.ps1
```

### 3. Check API Logs from Database
```powershell
.\check-api-logs.ps1 -ClientId <YOUR_CLIENT_ID> -Limit 20
```

## API Endpoint

**URL:** `POST /api/{company_id}/messages/send`

**Headers:**
- `X-API-Key: <your_api_key>`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "client_id": 123,
  "channel": "sms",
  "recipient": "254712345678",
  "body": "Your message here",
  "sender": "YOUR_SENDER_ID"  // Optional
}
```

**Response (Success):**
```json
{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "id": 123,
    "status": "sent",
    "provider_message_id": "msg_123456789"
  }
}
```

## Log Locations

1. **Laravel Log File:** `storage/logs/laravel.log`
   - All application logs including API requests
   - Use `watch-logs.ps1` to monitor in real-time

2. **API Logs Database:** `api_logs` table
   - Detailed API request/response logging
   - Includes request body, response body, response time, errors
   - Use `check-api-logs.ps1` to query

## Log Monitoring

### Real-Time Log Watching
The `watch-logs.ps1` script will:
- Show last 50 lines of log file
- Monitor for new entries in real-time
- Color-code log levels (ERROR=Red, WARNING=Yellow, INFO=Green)

### Database Log Query
The `check-api-logs.ps1` script will:
- Query the `api_logs` table
- Show recent API requests with full details
- Filter by client_id if provided
- Display request/response bodies

## Testing Checklist

- [ ] API key is valid and client account is active
- [ ] Client has sufficient balance
- [ ] Recipient phone number is in correct format (254XXXXXXXXX)
- [ ] Sender ID is approved (if provided)
- [ ] Check logs for any errors
- [ ] Verify message was sent successfully

## Common Issues

1. **401 Unauthorized**
   - Check API key is correct
   - Verify client account is active (`status = true`)

2. **422 Validation Error**
   - Check request body format matches API requirements
   - Ensure all required fields are present

3. **402 Payment Required**
   - Client has insufficient balance
   - Check balance via `/api/{company_id}/client/balance`

4. **500 Server Error**
   - Check Laravel logs for detailed error
   - Verify SMS gateway configuration

## Notes

- The API automatically logs all requests to both `laravel.log` and `api_logs` table
- Response times are logged in milliseconds
- API keys are masked in logs for security
- All requests include IP address and user agent logging







