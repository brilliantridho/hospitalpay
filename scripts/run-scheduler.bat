@echo off
REM Hospital Pay - Laravel Scheduler Runner
REM This script runs the Laravel scheduler every minute

cd /d D:\R\hospitalpay
php artisan schedule:run >> NUL 2>&1
