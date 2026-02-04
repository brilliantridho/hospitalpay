# Fix Hospital Pay Scheduler - Jalankan sebagai Administrator
# Klik kanan file ini -> Run with PowerShell (sebagai Administrator)

Write-Host "Memperbaiki Hospital Pay Scheduler..." -ForegroundColor Cyan
Write-Host ""

# Hapus task yang lama jika ada
Write-Host "1. Menghapus task scheduler lama..." -ForegroundColor Yellow
$null = schtasks /Delete /TN "HospitalPay-Scheduler" /F 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   Task lama berhasil dihapus" -ForegroundColor Green
} else {
    Write-Host "   Tidak ada task lama" -ForegroundColor Gray
}

# Buat task baru dengan konfigurasi yang benar
Write-Host ""
Write-Host "2. Membuat task scheduler baru..." -ForegroundColor Yellow

$action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument "/c D:\R\hospitalpay\run-scheduler.bat" -WorkingDirectory "D:\R\hospitalpay"

# Trigger: setiap 1 menit, tanpa batas waktu
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).Date
$trigger.Repetition = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) | Select-Object -ExpandProperty Repetition

$principal = New-ScheduledTaskPrincipal -UserId "$env:USERDOMAIN\$env:USERNAME" -LogonType Interactive -RunLevel Highest

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -DontStopOnIdleEnd

try {
    $result = Register-ScheduledTask -TaskName "HospitalPay-Scheduler" -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description "Hospital Pay Auto Scheduler - Runs Laravel scheduler every minute" -Force -ErrorAction Stop
    
    Write-Host "   Task berhasil dibuat!" -ForegroundColor Green
}
catch {
    Write-Host "   Gagal membuat task: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Tekan Enter untuk keluar..."
    Read-Host
    exit 1
}

# Verifikasi task
Write-Host ""
Write-Host "3. Memverifikasi task..." -ForegroundColor Yellow
$task = Get-ScheduledTask -TaskName "HospitalPay-Scheduler" -ErrorAction SilentlyContinue

if ($task) {
    Write-Host "   Task ditemukan: $($task.TaskName)" -ForegroundColor Green
    Write-Host "   Status: $($task.State)" -ForegroundColor Green
    
    # Jalankan task sekali untuk test
    Write-Host ""
    Write-Host "4. Menjalankan test pertama kali..." -ForegroundColor Yellow
    Start-ScheduledTask -TaskName "HospitalPay-Scheduler"
    Start-Sleep -Seconds 2
    
    $taskInfo = Get-ScheduledTaskInfo -TaskName "HospitalPay-Scheduler"
    Write-Host "   Last Run: $($taskInfo.LastRunTime)" -ForegroundColor Cyan
    Write-Host "   Last Result: $($taskInfo.LastTaskResult)" -ForegroundColor Cyan
    
    Write-Host ""
    Write-Host "SELESAI! Task Scheduler berhasil diperbaiki!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Scheduler akan berjalan setiap 1 menit" -ForegroundColor White
    Write-Host "Laporan akan dikirim setiap hari jam 16:10 WIB" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host "   Task tidak ditemukan!" -ForegroundColor Red
    exit 1
}

# Tampilkan schedule tasks
Write-Host "5. Schedule yang terdaftar:" -ForegroundColor Yellow
Set-Location D:\R\hospitalpay
php artisan schedule:list

Write-Host ""
Write-Host "Tekan Enter untuk keluar..."
Read-Host
