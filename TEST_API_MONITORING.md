# 🧪 Test Your API Monitoring System

## Quick Test Guide

### Step 1: Make a Test API Request

**Option A: Using cURL (Command Line)**
```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Test message for monitoring",
    "sender": "PRADY_TECH",
    "client_id": 1
  }'
```

**Option B: Test with Invalid API Key (to see failure logging)**
```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-Key: invalid_key_test_123" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "This should fail",
    "sender": "PRADY_TECH",
    "client_id": 1
  }'
```

**Option C: Test Balance Check**
```bash
curl -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  https://crm.pradytecai.com/api/1/client/balance
```

---

### Step 2: View the Logs

1. **Login to your dashboard:**
   ```
   https://crm.pradytecai.com/login
   ```

2. **Navigate to API Monitor:**
   - Look at the left sidebar
   - Under "Developer" section
   - Click on "API Monitor"
   
   Or go directly to:
   ```
   https://crm.pradytecai.com/api-monitor
   ```

3. **You should see:**
   - Your test request(s) in the table
   - Green badge for successful requests
   - Red badge for failed requests
   - Response time in milliseconds
   - IP address of the request
   - Timestamp

---

### Step 3: View Request Details

1. Click the **eye icon** (👁️) on any request
2. You'll see:
   - Complete request headers
   - Request body (JSON)
   - Response body (JSON)
   - Response time
   - Error message (if failed)
   - IP and User Agent

---

### Step 4: Test Filters

**Filter by Status:**
1. Select "Status: Failed" from dropdown
2. Click search
3. Should see only failed requests

**Filter by Client:**
1. Select "Prady Technologies" from Client dropdown
2. Click search
3. Should see only Prady Tech requests

**Filter by Date:**
1. Enter today's date in "From Date"
2. Click search
3. Should see only today's requests

---

## What to Look For

### ✅ Successful Request
- Green status badge (200)
- Success = ✅ True
- Response time shown in green (if < 1s)
- Response body contains success data

### ❌ Failed Request
- Red status badge (401, 500, etc.)
- Success = ❌ False
- Error message displayed
- Row highlighted in red

---

## Statistics to Check

At the top of the dashboard, verify:

1. **Today's Requests** - Should show your test requests count
2. **Successful** - Should show successful test count with % rate
3. **Failed** - Should show failed test count with % rate
4. **Avg Response Time** - Should show average in milliseconds

---

## Sidebar Badge

Look at the sidebar:
- "API Monitor" should show a badge with today's request count
- Badge is GREEN if all requests successful
- Badge is RED if any requests failed

---

## Real-Time Updates

The dashboard auto-refreshes every 30 seconds, so:
1. Leave the dashboard open
2. Make a new API request from another window
3. Wait up to 30 seconds
4. See the new request appear automatically

Or click the **Refresh button** for immediate update.

---

## Check Laravel Logs

Your API requests are also logged to Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

You should see entries like:
```
[2025-10-19 15:30:45] local.INFO: API Request {"client":"Prady Technologies","endpoint":"api/1/messages/send","method":"POST","ip":"127.0.0.1","response_time":"125.50ms"}
```

---

## Database Verification

Check that logs are being stored:

```sql
-- View recent API logs
SELECT 
    id,
    client_id,
    endpoint,
    method,
    response_status,
    success,
    response_time_ms,
    created_at
FROM api_logs 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## Test Checklist

- [ ] Made a successful API request
- [ ] Made a failed API request (invalid API key)
- [ ] Viewed API Monitor dashboard
- [ ] Saw requests in the table
- [ ] Clicked "View Details" on a request
- [ ] Filtered by status (success/failed)
- [ ] Filtered by client
- [ ] Checked statistics cards at top
- [ ] Saw sidebar badge with count
- [ ] Verified request in database

---

## Troubleshooting

### "No requests showing"

**Check:**
1. Did the migration run successfully?
   ```bash
   php artisan migrate:status
   ```
2. Is the API request actually reaching your server?
3. Check Laravel logs for errors
4. Verify routes are registered:
   ```bash
   php artisan route:list | grep api-monitor
   ```

### "Error viewing dashboard"

**Check:**
1. ApiLog model exists: `app/Models/ApiLog.php`
2. Migration created table: Check database for `api_logs` table
3. Routes are registered: Check `routes/web.php`

### "Requests not being logged"

**Check:**
1. API middleware is being used on API routes
2. Check `app/Http/Middleware/ApiAuth.php` for logging code
3. Look for exceptions in Laravel logs

---

## Expected Results

After running all tests, you should see:

**Dashboard:**
- ✅ 3+ requests in the table
- ✅ Mix of successful (green) and failed (red)
- ✅ Response times displayed
- ✅ Correct client names
- ✅ Accurate timestamps

**Details Page:**
- ✅ Complete request/response data
- ✅ Headers visible (API keys masked)
- ✅ JSON formatted nicely
- ✅ Error messages on failures

**Sidebar:**
- ✅ Badge showing count
- ✅ Red badge if failures exist
- ✅ Green badge if all successful

---

## Advanced Tests

### Test Performance Tracking

Make 10 quick requests:
```bash
for i in {1..10}; do
  curl -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
    https://crm.pradytecai.com/api/1/client/balance
  sleep 1
done
```

Then check:
- Average response time
- Min/Max response times
- Performance trends

### Test Different Endpoints

```bash
# Test SMS history
curl -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  "https://crm.pradytecai.com/api/1/sms/history?page=1"

# Test SMS statistics
curl -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  "https://crm.pradytecai.com/api/1/sms/statistics?period=week"
```

Then filter by endpoint to see each one.

---

## Success Criteria

✅ **System is working if:**
1. All API requests are logged
2. Dashboard displays requests correctly
3. Filters work properly
4. Details page shows complete info
5. Statistics are accurate
6. No errors in Laravel logs
7. Performance is good (< 1s page load)

---

**Test Date:** October 19, 2025  
**Status:** Ready for Testing  
**Version:** 1.0.0

