# Quick ngrok Installation Script
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   NGROK QUICK INSTALL" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$ngrokPath = "C:\ngrok"
$ngrokExe = "$ngrokPath\ngrok.exe"

# Check if already installed
if (Test-Path $ngrokExe) {
    Write-Host "[OK] ngrok is already installed at: $ngrokExe" -ForegroundColor Green
    Write-Host "`nVersion:" -ForegroundColor Yellow
    & $ngrokExe version
} else {
    Write-Host "[INFO] ngrok not found. Installing...`n" -ForegroundColor Yellow
    
    # Create directory
    if (!(Test-Path $ngrokPath)) {
        New-Item -ItemType Directory -Path $ngrokPath | Out-Null
        Write-Host "[OK] Created directory: $ngrokPath" -ForegroundColor Green
    }
    
    Write-Host "`n[ACTION REQUIRED] Please download ngrok:" -ForegroundColor Red
    Write-Host "1. Go to: https://ngrok.com/download" -ForegroundColor White
    Write-Host "2. Download the Windows version (ZIP)" -ForegroundColor White
    Write-Host "3. Extract ngrok.exe" -ForegroundColor White
    Write-Host "4. Move ngrok.exe to: $ngrokPath" -ForegroundColor White
    Write-Host "5. Run this script again" -ForegroundColor White
    Write-Host "`nOpening download page..." -ForegroundColor Yellow
    Start-Process "https://ngrok.com/download"
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   USAGE" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan
Write-Host "To start ngrok:" -ForegroundColor White
Write-Host "  $ngrokExe http 80" -ForegroundColor Yellow
Write-Host "`nOr if added to PATH:" -ForegroundColor White
Write-Host "  ngrok http 80" -ForegroundColor Yellow
Write-Host "`n"



