# Check API Logs from Database
# Usage: .\check-api-logs.ps1 [-ClientId <ID>] [-Limit <N>]

param(
    [int]$ClientId = 0,
    [int]$Limit = 20
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "API Logs from Database" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Run artisan tinker command to query API logs
$tinkerScript = @"
use App\Models\ApiLog;
use App\Models\Client;

`$query = ApiLog::with('client')->latest();

if ($ClientId > 0) {
    `$query->where('client_id', $ClientId);
}

`$logs = `$query->take($Limit)->get();

foreach (`$logs as `$log) {
    echo '[' . `$log->created_at . '] ' . `$log->method . ' ' . `$log->endpoint . PHP_EOL;
    echo '  Client: ' . (`$log->client ? `$log->client->company_name : 'N/A') . ' (ID: ' . `$log->client_id . ')' . PHP_EOL;
    echo '  Status: ' . `$log->response_status . ' (' . (`$log->success ? 'SUCCESS' : 'FAILED') . ')' . PHP_EOL;
    echo '  Response Time: ' . `$log->response_time_ms . 'ms' . PHP_EOL;
    if (`$log->error_message) {
        echo '  Error: ' . `$log->error_message . PHP_EOL;
    }
    echo '  Request Body: ' . json_encode(`$log->request_body, JSON_PRETTY_PRINT) . PHP_EOL;
    echo '  Response Body: ' . json_encode(`$log->response_body, JSON_PRETTY_PRINT) . PHP_EOL;
    echo '---' . PHP_EOL;
}
"@

# Save script to temp file
$tempFile = [System.IO.Path]::GetTempFileName() + ".php"
$tinkerScript | Out-File -FilePath $tempFile -Encoding UTF8

Write-Host "Querying API logs..." -ForegroundColor Yellow
Write-Host ""

# Execute via artisan tinker
php artisan tinker --execute (Get-Content $tempFile -Raw)

# Clean up
Remove-Item $tempFile -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan







