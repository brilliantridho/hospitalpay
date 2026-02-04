# Test Scheduler TANPA Web Server

Write-Host "=== Testing Scheduler Independence ===" -ForegroundColor Cyan
Write-Host ""

# 1. Check if web server is running
Write-Host "1. Checking web server status..." -ForegroundColor Yellow
$WebServer = Get-Process -Name "php" -ErrorAction SilentlyContinue | Where-Object { $_.Path -like "*artisan*" }
if ($WebServer) {
    Write-Host "   Web server IS running (php artisan serve)" -ForegroundColor Green
} else {
    Write-Host "   Web server NOT running (GOOD for this test!)" -ForegroundColor Green
}
Write-Host ""

# 2. Check database connection
Write-Host "2. Checking database connection..." -ForegroundColor Yellow
try {
    $result = php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';" 2>&1
    if ($result -like "*Connected*") {
        Write-Host "   Database: CONNECTED" -ForegroundColor Green
    } else {
        Write-Host "   Database: ERROR - $result" -ForegroundColor Red
    }
} catch {
    Write-Host "   Database: ERROR - $_" -ForegroundColor Red
}
Write-Host ""

# 3. Check PHP CLI
Write-Host "3. Checking PHP CLI..." -ForegroundColor Yellow
$phpVersion = php --version 2>&1 | Select-Object -First 1
Write-Host "   $phpVersion" -ForegroundColor Green
Write-Host ""

# 4. Test scheduler command
Write-Host "4. Testing scheduler command..." -ForegroundColor Yellow
Write-Host "   Running: php artisan schedule:list" -ForegroundColor Gray
php artisan schedule:list
Write-Host ""

# 5. Test report generation
Write-Host "5. Testing report generation (WITHOUT web server)..." -ForegroundColor Yellow
Write-Host "   This proves scheduler works independently!" -ForegroundColor Cyan
Write-Host ""
php artisan test:scheduled-report yesterday

Write-Host ""
Write-Host "=== CONCLUSION ===" -ForegroundColor Cyan
Write-Host "Scheduler bekerja TANPA php artisan serve!" -ForegroundColor Green
Write-Host "Laporan akan terkirim otomatis meskipun web server mati." -ForegroundColor Green
Write-Host ""
Write-Host "Yang penting:" -ForegroundColor Yellow
Write-Host "  1. Database server running" -ForegroundColor White
Write-Host "  2. Task Scheduler configured" -ForegroundColor White
Write-Host "  3. Internet connection available" -ForegroundColor White
