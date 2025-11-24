# Performance & Scalability Optimization Guide

This document outlines the performance optimizations implemented in the Bulk SMS application.

## Table of Contents

1. [Database Optimization](#database-optimization)
2. [Caching Strategy](#caching-strategy)
3. [Queue System](#queue-system)
4. [API Response Optimization](#api-response-optimization)
5. [File Storage](#file-storage)

## Database Optimization

### Indexes

Database indexes have been added to frequently queried columns to improve query performance:

- **messages**: `client_id`, `status`, `channel`, `created_at`, composite indexes
- **contacts**: `client_id`, `contact`, composite indexes
- **campaigns**: `client_id`, `status`, `scheduled_at`
- **api_logs**: `client_id`, `success`, `created_at`, composite indexes
- **wallet_transactions**: `client_id`, `status`, `type`, `created_at`
- **conversations**: `client_id`, `contact_id`, `last_message_at`
- **users**: `client_id`, `email`
- **channels**: `client_id`, `name`

**Migration**: Run `php artisan migrate` to apply indexes.

### Eager Loading

Controllers now use eager loading to prevent N+1 query problems:

```php
// Before (N+1 problem)
$campaigns = Campaign::where('client_id', $clientId)->get();
// Each campaign->client access triggers a new query

// After (eager loading)
$campaigns = Campaign::where('client_id', $clientId)
    ->with('client')
    ->get();
// Client relationship loaded in a single query
```

**Updated Controllers**:
- `DashboardController`
- `Api/ContactController`
- `Api/CampaignController`
- `Api/SmsController`
- `ApiMonitorController`

### Query Caching

Frequently accessed data is cached:

```php
// Cache clients list for 5 minutes
$clients = Cache::remember('api_monitor_clients', 300, function () {
    return Client::select('id', 'name')->get();
});
```

## Caching Strategy

### Client Settings Cache

The `ClientSettingsCache` service provides caching for client settings:

```php
use App\Services\Cache\ClientSettingsCache;

// Get cached client settings
$settings = ClientSettingsCache::get($clientId);

// Get or remember
$settings = ClientSettingsCache::remember($clientId, function () use ($client) {
    return [
        'balance' => $client->balance,
        'price_per_unit' => $client->price_per_unit,
        // ...
    ];
});

// Invalidate cache on update
ClientSettingsCache::invalidate($clientId);
```

**Cache TTL**: 1 hour (3600 seconds)

### Rate Limit Cache

The `RateLimitCache` service manages API rate limiting counters:

```php
use App\Services\Cache\RateLimitCache;

// Increment counter
$current = RateLimitCache::increment($key, 60);

// Check if exceeded
if (RateLimitCache::isExceeded($key, $limit)) {
    // Handle rate limit
}

// Get remaining attempts
$remaining = RateLimitCache::remaining($key, $limit);
```

**Cache TTL**: 60 seconds (1 minute)

### Redis Configuration

Redis is now the default cache and session driver:

**Environment Variables**:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

**Benefits**:
- Faster cache operations
- Shared session storage across servers
- Better queue performance
- Atomic operations for rate limiting

## Queue System

### Configuration

The queue system is configured to use Redis by default:

```env
QUEUE_CONNECTION=redis
```

**Queue Drivers Available**:
- `redis` (recommended for production)
- `database` (fallback if Redis unavailable)
- `sync` (for development/testing)

### Job Retries

Jobs include automatic retry logic:

```php
class SendMessageJob implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min
}
```

**Retry Strategy**:
- Maximum 3 attempts
- Exponential backoff: 1 minute, 5 minutes, 15 minutes
- Failed jobs logged to `failed_jobs` table

### Queue Monitoring

Monitor queue status:

```bash
# Check queue size and failed jobs
php artisan queue:monitor

# Process jobs
php artisan queue:work

# Process specific queue
php artisan queue:work --queue=high,default

# Retry failed jobs
php artisan queue:retry all
```

## API Response Optimization

### Pagination

All list endpoints now support pagination:

```php
// API endpoints with pagination
GET /api/{company_id}/contacts?per_page=50&page=1
GET /api/{company_id}/campaigns?per_page=50&page=1
GET /api/{company_id}/sms/history?per_page=50&page=1
```

**Default**: 50 items per page
**Maximum**: Configurable per endpoint

### Response Compression

The `CompressResponse` middleware automatically compresses API responses:

- **Algorithm**: Gzip
- **Compression Level**: 6 (balanced)
- **Minimum Size**: 1KB (smaller responses not compressed)
- **Content Types**: JSON and text responses

**Benefits**:
- Reduced bandwidth usage
- Faster response times
- Better mobile experience

**Headers**:
- `Content-Encoding: gzip`
- `Vary: Accept-Encoding`

### Response Caching

Cache frequently accessed API responses:

```php
// Cache API response for 5 minutes
return Cache::remember("api_response:{$key}", 300, function () {
    return $this->generateResponse();
});
```

## File Storage

### S3 Configuration

Amazon S3 is configured for file storage:

**Environment Variables**:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
AWS_URL=https://your_bucket.s3.amazonaws.com
```

**Benefits**:
- Scalable storage
- CDN integration
- Reduced server load
- Better reliability

### Image Optimization

The `ImageOptimizationService` optimizes uploaded images. **Note**: This requires the `intervention/image` package:

```bash
composer require intervention/image
```

```php
use App\Services\ImageOptimizationService;

$service = new ImageOptimizationService();

// Optimize and store
$path = $service->optimizeAndStore(
    $uploadedFile,
    's3',
    'avatars',
    800,  // max width
    800,  // max height
    85    // quality
);

// Create thumbnail
$thumbnail = $service->createThumbnail($path, 's3', 200, 200);
```

**Features**:
- Automatic resizing
- Quality optimization
- Thumbnail generation
- Aspect ratio preservation

## Performance Monitoring

### Queue Monitoring

```bash
# Monitor queue status
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry {job_id}
```

### Database Query Monitoring

Enable query logging in development:

```php
// In AppServiceProvider
DB::listen(function ($query) {
    Log::info($query->sql, [
        'bindings' => $query->bindings,
        'time' => $query->time
    ]);
});
```

### Cache Statistics

Check cache performance:

```php
// Get cache statistics (Redis)
$stats = Cache::getRedis()->info('stats');
```

## Best Practices

1. **Always use eager loading** when accessing relationships
2. **Cache frequently accessed data** with appropriate TTL
3. **Use Redis** for production environments
4. **Monitor queue backlog** regularly
5. **Optimize images** before storage
6. **Use pagination** for all list endpoints
7. **Enable compression** for API responses
8. **Monitor database indexes** usage

## Troubleshooting

### Redis Connection Issues

If Redis is unavailable, fallback to database:

```env
CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Queue Not Processing

1. Check queue worker is running: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Check queue size: `php artisan queue:monitor`

### Slow Queries

1. Check if indexes are applied: `php artisan migrate:status`
2. Enable query logging to identify slow queries
3. Use `EXPLAIN` to analyze query plans
4. Consider adding composite indexes for common query patterns

## Migration Checklist

- [ ] Run database migrations: `php artisan migrate`
- [ ] Configure Redis connection
- [ ] Update environment variables
- [ ] Start queue worker: `php artisan queue:work`
- [ ] Configure S3 credentials (if using cloud storage)
- [ ] Test API endpoints with pagination
- [ ] Verify response compression
- [ ] Monitor queue and cache performance

