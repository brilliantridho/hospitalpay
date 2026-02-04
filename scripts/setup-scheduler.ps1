# Hospital Pay - Auto Setup Windows Task Scheduler
# Run as Administrator

$TaskName = "Hospital Pay - Laravel Scheduler"
$TaskDescription = "Run Laravel scheduler every minute to check for scheduled tasks"
$ScriptPath = "D:\R\hospitalpay\run-scheduler.bat"
$WorkingDir = "D:\R\hospitalpay"

Write-Host "Setting up Windows Task Scheduler for Hospital Pay..." -ForegroundColor Cyan
Write-Host ""

# Check if task already exists
$ExistingTask = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue

if ($ExistingTask) {
    Write-Host "WARNING: Task already exists. Removing old task..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
    Write-Host "SUCCESS: Old task removed" -ForegroundColor Green
}

# Create action
$Action = New-ScheduledTaskAction `
    -Execute "cmd.exe" `
    -Argument "/c `"$ScriptPath`"" `
    -WorkingDirectory $WorkingDir

# Create trigger (every minute, indefinitely)
$Trigger = New-ScheduledTaskTrigger -Once -At (Get-Date)
$Trigger.Repetition = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) | Select-Object -ExpandProperty Repetition

# Create settings
$Settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RunOnlyIfNetworkAvailable:$false `
    -DontStopOnIdleEnd

# Create principal (run whether user is logged on or not)
$Principal = New-ScheduledTaskPrincipal `
    -UserId "$env:USERDOMAIN\$env:USERNAME" `
    -LogonType S4U `
    -RunLevel Highest

# Register task
try {
    Register-ScheduledTask `
        -TaskName $TaskName `
        -Description $TaskDescription `
        -Action $Action `
        -Trigger $Trigger `
        -Settings $Settings `
        -Principal $Principal `
        -Force | Out-Null
    
    Write-Host ""
    Write-Host "SUCCESS! Task Scheduler setup completed!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Task Details:" -ForegroundColor Cyan
    Write-Host "   Name: $TaskName"
    Write-Host "   Frequency: Every 1 minute"
    Write-Host "   Script: $ScriptPath"
    Write-Host "   Status: Ready"
    Write-Host ""
    Write-Host "Schedule Configuration:" -ForegroundColor Cyan
    Write-Host "   Report Time: 01:00 AM daily (Asia/Jakarta)"
    Write-Host "   Next Run: Tomorrow at 01:00 AM"
    Write-Host ""
    Write-Host "Testing:" -ForegroundColor Yellow
    Write-Host "   1. Test command: php artisan test:scheduled-report"
    Write-Host "   2. Open Task Scheduler: taskschd.msc"
    Write-Host "   3. Right-click task -> Run"
    Write-Host "   4. Check Telegram for report"
    Write-Host ""
    Write-Host "Telegram notification will be sent automatically at 01:00 AM" -ForegroundColor Cyan
    Write-Host ""
    
} catch {
    Write-Host ""
    Write-Host "ERROR: Failed to create task" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)"
    Write-Host ""
    Write-Host "TIP: Try running PowerShell as Administrator" -ForegroundColor Yellow
    exit 1
}
