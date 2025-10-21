@echo off
REM ========================================
REM Laravel Task Scheduler for Windows
REM ========================================

cd C:\xampp\htdocs\BulkSms
php artisan schedule:run >> storage\logs\scheduler.log 2>&1

