# Watch Laravel Logs in Real-Time
# Usage: .\watch-logs.ps1

param(
    [string]$LogFile = "storage\logs\laravel.log",
    [int]$Lines = 50
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Watching Laravel Logs" -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Show last N lines
if (Test-Path $LogFile) {
    Write-Host "Last $Lines lines from $LogFile:" -ForegroundColor Yellow
    Write-Host "----------------------------------------" -ForegroundColor Gray
    Get-Content $LogFile -Tail $Lines
    Write-Host "----------------------------------------" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Watching for new entries..." -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host "Log file not found: $LogFile" -ForegroundColor Red
    Write-Host "Waiting for log file to be created..." -ForegroundColor Yellow
}

# Watch for new log entries
$lastSize = if (Test-Path $LogFile) { (Get-Item $LogFile).Length } else { 0 }

while ($true) {
    Start-Sleep -Seconds 1
    
    if (Test-Path $LogFile) {
        $currentSize = (Get-Item $LogFile).Length
        
        if ($currentSize -gt $lastSize) {
            $stream = [System.IO.File]::Open($LogFile, [System.IO.FileMode]::Open, [System.IO.FileAccess]::Read, [System.IO.FileShare]::ReadWrite)
            $stream.Position = $lastSize
            $reader = New-Object System.IO.StreamReader($stream)
            
            while ($null -ne ($line = $reader.ReadLine())) {
                # Color code log levels
                if ($line -match "\.ERROR:") {
                    Write-Host $line -ForegroundColor Red
                } elseif ($line -match "\.WARNING:") {
                    Write-Host $line -ForegroundColor Yellow
                } elseif ($line -match "\.INFO:") {
                    Write-Host $line -ForegroundColor Green
                } elseif ($line -match "API Request") {
                    Write-Host $line -ForegroundColor Cyan
                } else {
                    Write-Host $line
                }
            }
            
            $reader.Close()
            $stream.Close()
            $lastSize = $currentSize
        }
    }
}







