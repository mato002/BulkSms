## Monitoring & Observability

This project now ships with first-class monitoring primitives covering application performance, uptime, and business KPIs.

### 1. Application Performance Monitoring (APM)

- **Vendor**: [Sentry Laravel SDK](https://docs.sentry.io/platforms/php/guides/laravel/)
- **Installation**: `composer require sentry/sentry-laravel`
- **Configuration file**: `config/sentry.php`
- **Log channel**: Added a `sentry` channel in `config/logging.php` and included it in the default stack.
- **HTTP tracing**: `\Sentry\Laravel\Tracing\Middleware::class` is registered on both the `web` and `api` middleware stacks.

Configure the SDK via environment variables:

```
SENTRY_LARAVEL_DSN=your_dsn_here
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_PROFILES_SAMPLE_RATE=0.2
SENTRY_SEND_DEFAULT_PII=false
SENTRY_ENVIRONMENT=production
```

> Adjust sample rates to match the volume you are comfortable sending to Sentry (range `0.0` – `1.0`).

**Filtering noisy events**

The `ignore_commands` option was removed in Sentry Laravel SDK v4. If you want to suppress scheduler heartbeat commands (or any other events) use the `before_send` hook that now ships in `config/sentry.php`. Return `null` to drop an event before it leaves your application.

### 2. Uptime & System Health

- **Endpoint**: `GET /api/health`
- **Controller**: `App\Http\Controllers\Monitoring\HealthCheckController`
- **Checks performed**:
  - Database connectivity & latency
  - Cache read/write sanity check
  - Queue backlog (with configurable threshold)
  - Disk space availability
- **Status codes**:
  - `200 OK` – all checks healthy
  - `503 Service Unavailable` – one or more checks degraded or failing

Configure the queue backlog threshold using:

```
MONITORING_QUEUE_BACKLOG_THRESHOLD=50
```

> Tip: Integrate this endpoint with external uptime monitors (UptimeRobot, BetterStack, Pingdom, etc.) and set alerts on non-200 responses.

### 3. Business Metrics (KPIs)

- **Service**: `App\Services\Monitoring\BusinessMetricsService`
- **View**: `resources/views/dashboard.blade.php` – new KPI cards for tenants and platform admins.
- **Metrics tracked**:
  - Messages sent (current vs previous month)
  - Revenue/top-ups (current vs previous month)
  - New contacts (current vs previous month)
  - Campaign launches (current vs previous month)
  - Platform-wide aggregates for administrators (messages, revenue, new clients, active clients)

All metrics compute month-over-month deltas to highlight growth trends.

### 4. Next Steps

- **Alerting**: Wire Sentry alerts and health-check monitors into your incident response tooling (Slack, PagerDuty, email, etc.).
- **Dashboards**: Use Sentry dashboards or BI tooling (Metabase, Power BI, etc.) to visualise long-term trends.
- **Data warehouse**: Consider exporting KPI data to a warehouse for deeper analysis (retention, funnel metrics, etc.).

### 5. Verification Checklist

1. Set `SENTRY_LARAVEL_DSN` in the environment and confirm events appear in Sentry.
2. Call `GET /api/health` and verify JSON output (`status: ok`).
3. Trigger queue backlog or DB failure scenarios to validate degraded/error responses.
4. Visit `/dashboard` as both a tenant and an admin to confirm KPI cards render with meaningful data.


