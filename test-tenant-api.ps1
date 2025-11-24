# Test Tenant API - Send SMS
# Usage: .\test-tenant-api.ps1 -ClientId <ID> -ApiKey <KEY> -Recipient <PHONE>

param(
    [Parameter(Mandatory=$true)]
    [string]$ClientId,
    
    [Parameter(Mandatory=$true)]
    [string]$ApiKey,
    
    [Parameter(Mandatory=$true)]
    [string]$Recipient,
    
    [string]$Message = "Test SMS from BulkSMS API",
    [string]$Sender = "",
    [string]$BaseUrl = "http://127.0.0.1:8000"
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Testing Tenant API - Send SMS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Build the endpoint URL
$endpoint = "$BaseUrl/api/$ClientId/messages/send"
Write-Host "Endpoint: $endpoint" -ForegroundColor Yellow
Write-Host ""

# Prepare request body (using correct API format)
$body = @{
    client_id = [int]$ClientId
    channel = "sms"
    recipient = $Recipient
    body = $Message
} | ConvertTo-Json

if ($Sender) {
    $bodyObj = $body | ConvertFrom-Json
    $bodyObj | Add-Member -MemberType NoteProperty -Name "sender" -Value $Sender -Force
    $body = $bodyObj | ConvertTo-Json
}

Write-Host "Request Body:" -ForegroundColor Yellow
Write-Host $body -ForegroundColor Gray
Write-Host ""

# Make the API request
try {
    Write-Host "Sending request..." -ForegroundColor Yellow
    
    $response = Invoke-RestMethod -Uri $endpoint `
        -Method POST `
        -Headers @{
            "X-API-Key" = $ApiKey
            "Content-Type" = "application/json"
            "Accept" = "application/json"
        } `
        -Body $body `
        -ErrorAction Stop
    
    Write-Host ""
    Write-Host "✅ SUCCESS!" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Yellow
    $response | ConvertTo-Json -Depth 10 | Write-Host -ForegroundColor Green
    
} catch {
    Write-Host ""
    Write-Host "❌ ERROR!" -ForegroundColor Red
    Write-Host "Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response Body:" -ForegroundColor Yellow
        Write-Host $responseBody -ForegroundColor Red
    } else {
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Check logs:" -ForegroundColor Cyan
Write-Host "  - Laravel Log: storage\logs\laravel.log" -ForegroundColor Gray
Write-Host "  - API Logs DB: api_logs table" -ForegroundColor Gray
Write-Host "========================================" -ForegroundColor Cyan







