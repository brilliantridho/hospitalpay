# Hospital Pay - Laravel Scheduler Runner (PowerShell)
# This script runs the Laravel scheduler every minute

$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
Write-Host "[$timestamp] Running Laravel Scheduler..." -ForegroundColor Cyan

Set-Location "D:\R\hospitalpay"
$output = php artisan schedule:run 2>&1

if ($output -match "No scheduled commands are ready to run") {
    Write-Host "[$timestamp] ⏰ Waiting - No tasks scheduled for this time" -ForegroundColor Yellow
    Write-Host "[$timestamp] Next: Daily report at 23:30" -ForegroundColor Gray
} elseif ($output -match "Running scheduled command") {
    Write-Host "[$timestamp] ✅ Task executed successfully!" -ForegroundColor Green
    Write-Host $output -ForegroundColor White
} else {
    Write-Host "[$timestamp] Output:" -ForegroundColor White
    Write-Host $output
}

Write-Host ""
