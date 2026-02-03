# Hospital Pay - Laravel Scheduler Runner (PowerShell)
# This script runs the Laravel scheduler every minute

Set-Location "D:\R\hospitalpay"
php artisan schedule:run *> $null
