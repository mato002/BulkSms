# 🔍 API Request Monitoring System - Complete Setup

## ✅ What Was Installed

I've set up a comprehensive API request monitoring system that tracks every API call made to your platform.

---

## 📊 Features

### 1. **Automatic Request Logging**
   - Every API request is automatically logged
   - Tracks success and failures
   - Measures response times
   - Records IP addresses and user agents
   - Captures request and response data

### 2. **Security Features**
   - API keys are masked in logs
   - Sensitive data (passwords) are automatically redacted
   - Headers are sanitized before storage

### 3. **Real-time Monitoring Dashboard**
   - View all API requests in real-time
   - Filter by client, status, endpoint, method, date
   - Auto-refreshes every 30 seconds
   - Color-coded for success/failure

### 4. **Statistics & Analytics**
   - Total requests today/week/month
   - Success rate calculations
   - Average response time tracking
   - Requests by endpoint
   - Hourly activity charts

---

## 🚀 How to Access

### View API Monitor Dashboard

```
URL: https://crm.pradytecai.com/api-monitor
```

**Login to your account and navigate to:**
- Dashboard → Click "API Monitor" in the sidebar
- Or directly visit: `/api-monitor`

---

## 📋 What Gets Logged

For each API request, the system logs:

| Field | Description |
|-------|-------------|
| **Client** | Which organization made the request |
| **Endpoint** | API endpoint called (e.g., `/api/1/messages/send`) |
| **Method** | HTTP method (GET, POST, PUT, DELETE) |
| **IP Address** | Client's IP address |
| **Request Headers** | All HTTP headers (API keys masked) |
| **Request Body** | JSON request payload (passwords masked) |
| **Response Status** | HTTP status code (200, 401, 500, etc.) |
| **Response Body** | API response data |
| **Response Time** | How long the request took (in milliseconds) |
| **Success/Failure** | Whether the request succeeded |
| **Error Message** | Any error messages |
| **Timestamp** | When the request was made |

---

## 📈 Dashboard Features

### Statistics Cards

At the top of the dashboard you'll see:

1. **Today's Requests** - Total API calls today
2. **Successful** - Successful requests with success rate %
3. **Failed** - Failed requests with failure rate %
4. **Avg Response Time** - Average API response time in milliseconds

### Filters

Filter logs by:
- **Client** - See requests from specific organizations
- **Status** - Success or Failed only
- **Method** - GET, POST, PUT, DELETE
- **Date Range** - From/To dates
- **Endpoint** - Search by API endpoint

### Request List

View all requests with:
- Time (with "X minutes ago" format)
- Client name and ID
- HTTP method (color-coded badges)
- Endpoint path
- IP address
- Status code (success/failure badge)
- Response time (green < 1s, yellow < 3s, red > 3s)
- View Details button

### Request Details Page

Click "View Details" on any request to see:
- Complete request overview
- Full request headers
- Complete request body
- Full response body
- User agent information
- Error details (if failed)

---

## 🔔 Monitoring Tips

### Check for Issues

**High Failure Rate?**
- Filter by `Status: Failed`
- Look for patterns in the errors
- Check which client is having issues
- Review error messages

**Slow Response Times?**
- Sort by response time
- Look for consistently slow endpoints
- Check if specific clients are affected
- Consider optimization

**Unusual Activity?**
- Check IP addresses for suspicious requests
- Look for unusual request patterns
- Monitor failed authentication attempts

---

## 📊 Example Queries

### See All Failed Requests Today
```
Navigate to: /api-monitor
Select: Status = Failed
```

### Monitor Prady Tech's API Usage
```
Navigate to: /api-monitor
Select: Client = Prady Technologies
```

### Check Slow Requests
```
Look for requests with red response time badges (> 3000ms)
```

### View All SMS Send Requests
```
Navigate to: /api-monitor
Enter in search: messages/send
```

---

## 🗄️ Database Table

All logs are stored in the `api_logs` table:

```sql
-- View recent API requests
SELECT * FROM api_logs ORDER BY created_at DESC LIMIT 20;

-- Count requests by client today
SELECT client_id, COUNT(*) as total 
FROM api_logs 
WHERE DATE(created_at) = CURDATE() 
GROUP BY client_id;

-- Average response time per endpoint
SELECT endpoint, AVG(response_time_ms) as avg_time 
FROM api_logs 
GROUP BY endpoint 
ORDER BY avg_time DESC;

-- Failed requests in last hour
SELECT * FROM api_logs 
WHERE success = 0 
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

---

## 🧹 Cleanup (Optional)

To prevent the database from growing too large:

### Automatic Cleanup (Recommended)

Add this to your `app/Console/Kernel.php` schedule:

```php
protected function schedule(Schedule $schedule)
{
    // Delete API logs older than 30 days
    $schedule->call(function () {
        \App\Models\ApiLog::where('created_at', '<', now()->subDays(30))->delete();
    })->daily();
}
```

### Manual Cleanup

Navigate to `/api-monitor` and use the cleanup feature (if added), or run:

```sql
DELETE FROM api_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 📱 Real-Time Monitoring

The dashboard auto-refreshes every 30 seconds to show the latest requests.

### API Endpoints for Integration

If you want to build custom dashboards or alerts:

**Get Recent Activity:**
```
GET /api-monitor-activity
Returns last 20 API requests as JSON
```

**Get Statistics:**
```
GET /api-monitor-stats?period=week&client_id=1
Returns statistics for specific period and client
```

---

## 🔧 Configuration

### Disable Logging (if needed)

If you need to temporarily disable logging, edit `app/Http/Middleware/ApiAuth.php` and comment out the logging calls.

### Adjust Auto-refresh

Edit `resources/views/api-monitor/index.blade.php`:

```javascript
// Change from 30000 (30 seconds) to desired interval
setInterval(refreshData, 60000); // 60 seconds
```

---

## 🎯 Use Cases

### 1. Debugging Client Issues
"Prady Tech says SMS isn't sending"
1. Go to API Monitor
2. Filter by Client = Prady Tech
3. Look for failed requests
4. Click "View Details" on failures
5. Check error messages

### 2. Performance Monitoring
1. Check average response time daily
2. Look for slow endpoints
3. Identify bottlenecks
4. Optimize as needed

### 3. Security Auditing
1. Monitor failed authentication attempts
2. Check for unusual IP addresses
3. Look for brute force patterns
4. Review suspicious activity

### 4. Usage Tracking
1. See which clients use the API most
2. Track popular endpoints
3. Identify peak usage times
4. Plan for scaling

### 5. Billing & Reporting
1. Count requests per client
2. Generate usage reports
3. Track API consumption
4. Create client invoices

---

## 📝 Files Created

### Database
- `database/migrations/2025_10_19_000000_create_api_logs_table.php`
- `app/Models/ApiLog.php`

### Controllers
- `app/Http/Controllers/ApiMonitorController.php`

### Views
- `resources/views/api-monitor/index.blade.php` (Dashboard)
- `resources/views/api-monitor/show.blade.php` (Details)

### Middleware (Updated)
- `app/Http/Middleware/ApiAuth.php` (Enhanced with logging)

### Routes (Added to web.php)
- `GET /api-monitor` - Dashboard
- `GET /api-monitor/{id}` - Request details
- `GET /api-monitor-stats` - Statistics API
- `GET /api-monitor-activity` - Recent activity API

---

## ✅ Next Steps

1. **Run Migration:**
   ```bash
   php artisan migrate
   ```

2. **Add to Navigation:**
   Add a link in your sidebar to `/api-monitor`

3. **Test It:**
   - Make a test API request
   - Visit `/api-monitor`
   - You should see the request logged

4. **Set Up Alerts (Optional):**
   - Create alerts for high failure rates
   - Notify admins of suspicious activity
   - Send daily summaries

---

## 🎉 Benefits

✅ **Know What's Happening** - See every API request in real-time  
✅ **Debug Faster** - Instantly see what went wrong  
✅ **Monitor Performance** - Track response times  
✅ **Improve Security** - Detect suspicious activity  
✅ **Better Support** - Help clients troubleshoot issues  
✅ **Track Usage** - Know who's using what  
✅ **Plan Capacity** - See usage patterns  

---

## 📞 Example Support Scenario

**Client:** "My API integration isn't working!"

**You:**
1. Go to `/api-monitor`
2. Filter by their client name
3. See their last 10 requests
4. Notice they're getting 401 errors
5. Check the error message: "Invalid API key"
6. Tell them: "Your API key has expired, here's a new one"

**Total time:** 2 minutes ✅

---

## 🔍 Quick Reference

**View Dashboard:**
```
https://crm.pradytecai.com/api-monitor
```

**Check Today's Stats:**
Look at the colored cards at the top

**Find Failed Requests:**
Status filter → Select "Failed"

**Search by Client:**
Client dropdown → Select client

**See Request Details:**
Click the eye icon on any request

**View Real-time:**
Page auto-refreshes every 30 seconds

---

**Setup Date:** October 19, 2025  
**Status:** ✅ Ready to Use  
**Version:** 1.0.0

