# Hospital Pay - Simple Scheduler Setup (No Admin Required)
# This creates a task that runs only when user is logged in

$TaskName = "Hospital Pay Scheduler"
$ScriptPath = "D:\R\hospitalpay\run-scheduler.bat"

Write-Host "Setting up Task Scheduler..." -ForegroundColor Cyan

# Remove existing task if any
$Existing = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
if ($Existing) {
    Write-Host "Removing existing task..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
}

# Create action
$Action = New-ScheduledTaskAction -Execute $ScriptPath

# Create trigger - At logon, repeat every minute
$Trigger = New-ScheduledTaskTrigger -AtLogOn
$Trigger.Repetition = (New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)).Repetition

# Register task (current user only, no admin needed)
Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger -Description "Run Laravel scheduler for Hospital Pay" | Out-Null

Write-Host ""
Write-Host "SUCCESS! Task created successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Task Name: $TaskName" -ForegroundColor Cyan
Write-Host "Runs every: 1 minute"
Write-Host "Report time: 01:00 AM daily"
Write-Host ""
Write-Host "To verify:" -ForegroundColor Yellow
Write-Host "  taskschd.msc -> Find '$TaskName'"
Write-Host ""
Write-Host "To test:" -ForegroundColor Yellow
Write-Host "  php artisan test:scheduled-report"
Write-Host ""
