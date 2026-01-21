@echo off
echo.
echo ============================================
echo   Laravel Migration Script
echo ============================================
echo.
echo Checking MySQL connection...
echo.

php verify_tables.php

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Could not connect to database
    echo.
    echo Please make sure:
    echo  1. XAMPP Control Panel is open
    echo  2. MySQL service is STARTED (green)
    echo  3. Try running this script again
    echo.
    pause
    exit /b 1
)

echo.
echo.
echo Running Laravel migrations...
echo.
php artisan migrate --force

echo.
echo Verifying migrations...
echo.
php verify_tables.php

echo.
echo ============================================
echo   Migration process complete!
echo ============================================
echo.
pause






