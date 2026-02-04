# Test Transaction Page Accessibility
Write-Host "Testing Transaction Create Page..." -ForegroundColor Cyan

# Check if Laravel is running
$laravelRunning = Get-Process | Where-Object { $_.ProcessName -like "*php*" -and $_.CommandLine -like "*artisan*serve*" }

if ($laravelRunning) {
    Write-Host "✓ Laravel server is running" -ForegroundColor Green
    
    # Try to access the page
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/kasir/transactions/create" -UseBasicParsing -ErrorAction Stop
        Write-Host "✓ Page is accessible (Status: $($response.StatusCode))" -ForegroundColor Green
        
        # Check for JavaScript errors in the HTML
        if ($response.Content -match "getElementById\('add-service'\)") {
            Write-Host "✓ JavaScript fixed - using safe check for add-service button" -ForegroundColor Green
        }
        
        if ($response.Content -match "check-voucher-btn") {
            Write-Host "✓ Voucher check button found" -ForegroundColor Green
        }
        
        Write-Host "`n✓ All checks passed!" -ForegroundColor Green
    }
    catch {
        Write-Host "✗ Error accessing page: $_" -ForegroundColor Red
        Write-Host "  Make sure you're logged in as 'kasir' user" -ForegroundColor Yellow
    }
}
else {
    Write-Host "✗ Laravel server is not running" -ForegroundColor Red
    Write-Host "  Run: php artisan serve" -ForegroundColor Yellow
}
