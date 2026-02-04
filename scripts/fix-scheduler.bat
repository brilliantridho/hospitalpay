@echo off
REM Fix Hospital Pay Scheduler
REM Klik kanan file ini -> Run as administrator

echo ========================================
echo Fix Hospital Pay Scheduler
echo ========================================
echo.

echo [1/3] Menghapus task scheduler lama...
schtasks /Delete /TN "HospitalPay-Scheduler" /F >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo     [OK] Task lama berhasil dihapus
) else (
    echo     [INFO] Tidak ada task lama
)
echo.

echo [2/3] Membuat task scheduler baru...
schtasks /Create /TN "HospitalPay-Scheduler" /TR "cmd /c D:\R\hospitalpay\run-scheduler.bat" /SC MINUTE /MO 1 /ST 00:00 /F /RL HIGHEST
if %ERRORLEVEL% EQU 0 (
    echo     [OK] Task berhasil dibuat!
) else (
    echo     [ERROR] Gagal membuat task!
    echo     Pastikan Anda menjalankan file ini sebagai Administrator
    pause
    exit /b 1
)
echo.

echo [3/3] Verifikasi dan test...
schtasks /Run /TN "HospitalPay-Scheduler"
timeout /t 2 >nul
schtasks /Query /TN "HospitalPay-Scheduler" /FO LIST | findstr /C:"Last Run Time" /C:"Status" /C:"Next Run Time"
echo.

echo ========================================
echo SELESAI!
echo ========================================
echo.
echo Scheduler akan berjalan setiap 1 menit
echo Laporan akan dikirim setiap hari jam 16:10 WIB
echo.
echo Schedule yang terdaftar:
cd /d D:\R\hospitalpay
php artisan schedule:list
echo.
echo.
pause
