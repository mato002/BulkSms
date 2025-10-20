@echo off
echo.
echo ========================================
echo   SETUP NGROK FOR LOCAL TESTING
echo ========================================
echo.

REM Check if ngrok is installed
where ngrok >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] ngrok not found!
    echo.
    echo Please download ngrok from: https://ngrok.com/download
    echo Install it and make sure it's in your PATH
    echo.
    pause
    exit /b
)

echo [OK] ngrok is installed
echo.

REM Get current .env APP_URL
echo Current APP_URL in .env:
findstr "APP_URL" .env
echo.

echo ========================================
echo   INSTRUCTIONS:
echo ========================================
echo.
echo 1. In a NEW terminal window, run:
echo    ngrok http 80
echo.
echo 2. Copy the HTTPS URL that ngrok shows
echo    (e.g., https://abc123.ngrok-free.app)
echo.
echo 3. Press any key here to continue...
pause >nul

echo.
echo 4. Paste your ngrok URL (the https one):
set /p NGROK_URL="   URL: "

if "%NGROK_URL%"=="" (
    echo [ERROR] No URL provided!
    pause
    exit /b
)

echo.
echo Updating .env file...

REM Backup current .env
copy .env .env.backup >nul
echo [OK] Backed up .env to .env.backup

REM Update APP_URL in .env
powershell -Command "(Get-Content .env) -replace 'APP_URL=.*', 'APP_URL=%NGROK_URL%' | Set-Content .env"

echo [OK] Updated APP_URL in .env
echo.

echo Clearing configuration cache...
php artisan config:clear

echo.
echo ========================================
echo   SETUP COMPLETE!
echo ========================================
echo.
echo Your APP_URL is now: %NGROK_URL%
echo.
echo NEXT STEPS:
echo   1. Send a message from your inbox
echo   2. Check the message contains your ngrok URL
echo   3. Click the link to test reply form
echo   4. Submit a reply and check inbox
echo.
echo To revert back to localhost:
echo   copy .env.backup .env
echo   php artisan config:clear
echo.
pause



