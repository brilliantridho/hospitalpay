# Hospital Pay - Scheduler Loop
# Script ini akan terus berjalan dan menjalankan scheduler setiap menit
# JANGAN TUTUP WINDOW INI!

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   SCHEDULER LOOP STARTED" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "⚠️  Script akan berjalan terus menerus" -ForegroundColor Yellow
Write-Host "⚠️  Tekan Ctrl+C untuk menghentikan" -ForegroundColor Yellow
Write-Host "" 

Set-Location "D:\R\hospitalpay"

while ($true) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] Running scheduler..." -ForegroundColor Cyan
    
    $output = php artisan schedule:run 2>&1
    
    if ($output -match "No scheduled commands are ready to run") {
        Write-Host "[$timestamp] ⏰ Waiting - Next: Daily report at 23:30" -ForegroundColor Gray
    } elseif ($output -match "Running scheduled command") {
        Write-Host "[$timestamp] ✅ TASK EXECUTED!" -ForegroundColor Green
        Write-Host $output -ForegroundColor White
    } else {
        Write-Host $output
    }
    
    # Sleep 60 detik (1 menit)
    Start-Sleep -Seconds 60
}
