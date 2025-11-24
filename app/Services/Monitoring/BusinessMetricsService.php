<?php

namespace App\Services\Monitoring;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BusinessMetricsService
{
    /**
     * Compile business KPIs for the dashboard.
     */
    public function forDashboard(int $clientId, bool $isAdmin): array
    {
        $now = CarbonImmutable::now();
        $startCurrentMonth = $now->startOfMonth();
        $startPreviousMonth = $startCurrentMonth->subMonth();
        $endPreviousMonth = $startCurrentMonth->subSecond();

        $tenantMetrics = $this->buildTenantMetrics(
            $clientId,
            $startCurrentMonth,
            $startPreviousMonth,
            $endPreviousMonth
        );

        $platformMetrics = $isAdmin
            ? $this->buildPlatformMetrics($startCurrentMonth, $startPreviousMonth, $endPreviousMonth)
            : [];

        return [
            'tenant' => $tenantMetrics,
            'platform' => $platformMetrics,
        ];
    }

    /**
     * Build metrics that are scoped to the authenticated tenant.
     */
    private function buildTenantMetrics(
        int $clientId,
        CarbonImmutable $startCurrentMonth,
        CarbonImmutable $startPreviousMonth,
        CarbonImmutable $endPreviousMonth
    ): array {
        $currentMessages = $this->countMessages($clientId, $startCurrentMonth, null);
        $previousMessages = $this->countMessages($clientId, $startPreviousMonth, $endPreviousMonth);

        $currentRevenue = $this->sumRevenue($clientId, $startCurrentMonth, null);
        $previousRevenue = $this->sumRevenue($clientId, $startPreviousMonth, $endPreviousMonth);

        $currentContacts = $this->countContacts($clientId, $startCurrentMonth, null);
        $previousContacts = $this->countContacts($clientId, $startPreviousMonth, $endPreviousMonth);

        $currentCampaigns = $this->countCampaigns($clientId, $startCurrentMonth, null);
        $previousCampaigns = $this->countCampaigns($clientId, $startPreviousMonth, $endPreviousMonth);

        return [
            $this->formatMetric(
                key: 'tenant_messages_month',
                label: 'Messages Sent (30d)',
                value: $currentMessages,
                previous: $previousMessages,
                description: 'Total outbound messages in the current month'
            ),
            $this->formatMetric(
                key: 'tenant_revenue_month',
                label: 'Revenue (30d)',
                value: $currentRevenue,
                previous: $previousRevenue,
                description: 'Completed top-ups in the current month',
                prefix: 'KSh '
            ),
            $this->formatMetric(
                key: 'tenant_contacts_month',
                label: 'New Contacts (30d)',
                value: $currentContacts,
                previous: $previousContacts,
                description: 'Growth of the contact list this month'
            ),
            $this->formatMetric(
                key: 'tenant_campaigns_month',
                label: 'Campaigns Launched (30d)',
                value: $currentCampaigns,
                previous: $previousCampaigns,
                description: 'Campaigns created or scheduled this month'
            ),
        ];
    }

    /**
     * Build metrics at the platform/operator level (admins only).
     */
    private function buildPlatformMetrics(
        CarbonImmutable $startCurrentMonth,
        CarbonImmutable $startPreviousMonth,
        CarbonImmutable $endPreviousMonth
    ): array {
        $currentMessages = $this->countMessages(null, $startCurrentMonth, null);
        $previousMessages = $this->countMessages(null, $startPreviousMonth, $endPreviousMonth);

        $currentRevenue = $this->sumRevenue(null, $startCurrentMonth, null);
        $previousRevenue = $this->sumRevenue(null, $startPreviousMonth, $endPreviousMonth);

        $currentClients = $this->countClients($startCurrentMonth, null);
        $previousClients = $this->countClients($startPreviousMonth, $endPreviousMonth);

        $activeClients = DB::table('clients')
            ->where('status', true)
            ->count();

        return [
            $this->formatMetric(
                key: 'platform_messages_month',
                label: 'Platform Messages (30d)',
                value: $currentMessages,
                previous: $previousMessages,
                description: 'Aggregate outbound messages across all clients'
            ),
            $this->formatMetric(
                key: 'platform_revenue_month',
                label: 'Platform Revenue (30d)',
                value: $currentRevenue,
                previous: $previousRevenue,
                description: 'Completed top-ups across all clients this month',
                prefix: 'KSh '
            ),
            $this->formatMetric(
                key: 'platform_new_clients',
                label: 'New Clients (30d)',
                value: $currentClients,
                previous: $previousClients,
                description: 'New tenant accounts created this month'
            ),
            $this->formatMetric(
                key: 'platform_active_clients',
                label: 'Active Clients',
                value: $activeClients,
                previous: null,
                description: 'Clients with an enabled status',
                showTrend: false
            ),
        ];
    }

    private function countMessages(?int $clientId, CarbonImmutable $startDate, ?CarbonImmutable $endDate): int
    {
        $query = DB::table('messages')
            ->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return (int) $query->count();
    }

    private function sumRevenue(?int $clientId, CarbonImmutable $startDate, ?CarbonImmutable $endDate): float
    {
        $query = DB::table('wallet_transactions')
            ->where('status', 'completed')
            ->where('type', 'topup')
            ->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return (float) $query->sum('amount');
    }

    private function countContacts(?int $clientId, CarbonImmutable $startDate, ?CarbonImmutable $endDate): int
    {
        $query = DB::table('contacts')
            ->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return (int) $query->count();
    }

    private function countCampaigns(?int $clientId, CarbonImmutable $startDate, ?CarbonImmutable $endDate): int
    {
        $query = DB::table('campaigns')
            ->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return (int) $query->count();
    }

    private function countClients(CarbonImmutable $startDate, ?CarbonImmutable $endDate): int
    {
        $query = DB::table('clients')
            ->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return (int) $query->count();
    }

    private function formatMetric(
        string $key,
        string $label,
        float|int $value,
        ?float $previous,
        string $description,
        string $prefix = '',
        string $suffix = '',
        bool $showTrend = true
    ): array {
        $trend = $showTrend ? $this->calculateTrend($value, $previous) : null;

        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'previous' => $previous,
            'description' => $description,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'trend' => $trend,
        ];
    }

    private function calculateTrend(float|int $current, ?float $previous): ?array
    {
        if ($previous === null) {
            return null;
        }

        if ($previous == 0) {
            if ($current == 0) {
                return [
                    'direction' => 'flat',
                    'percent' => 0.0,
                ];
            }

            return [
                'direction' => 'up',
                'percent' => 100.0,
            ];
        }

        $delta = $current - $previous;
        $percent = round(($delta / $previous) * 100, 1);

        return [
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
            'percent' => $percent,
        ];
    }
}





