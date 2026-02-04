<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Marketing\DashboardController as MarketingDashboard;
use App\Http\Controllers\Marketing\VoucherController;
use App\Http\Controllers\Marketing\InsuranceController as MarketingInsuranceController;
use App\Http\Controllers\Kasir\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ApiSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Redirect dashboard based on role
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'marketing') {
            return redirect()->route('marketing.dashboard');
        } else {
            return redirect()->route('kasir.transactions.index');
        }
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Report Routes (accessible by both roles for testing)
    Route::post('/reports/send-daily', [ReportController::class, 'sendDailyReport'])->name('reports.send-daily');
    Route::get('/reports/download-daily', [ReportController::class, 'downloadDailyReport'])->name('reports.download-daily');
    Route::post('/reports/test-telegram', [ReportController::class, 'testTelegram'])->name('reports.test-telegram');

    // API Sync Routes (accessible by both roles)
    Route::prefix('api-sync')->name('api-sync.')->group(function () {
        Route::get('/test-page', function () {
            return view('api-sync-test');
        })->name('test-page');
        Route::get('/test-auth', [ApiSyncController::class, 'testAuth'])->name('test-auth');
        Route::get('/test-procedure-prices', [ApiSyncController::class, 'testProcedurePrices'])->name('test-procedure-prices');
        Route::post('/sync-insurances', [ApiSyncController::class, 'syncInsurances'])->name('sync-insurances');
        Route::post('/sync-medical-services', [ApiSyncController::class, 'syncMedicalServices'])->name('sync-medical-services');
        Route::post('/sync-all', [ApiSyncController::class, 'syncAll'])->name('sync-all');
    });
});

// Marketing Routes
Route::middleware(['auth', 'role:marketing'])->prefix('marketing')->name('marketing.')->group(function () {
    Route::get('/dashboard', [MarketingDashboard::class, 'index'])->name('dashboard');
    Route::resource('vouchers', VoucherController::class);
    Route::get('/insurances', [MarketingInsuranceController::class, 'index'])->name('insurances.index');
    Route::get('/insurances/{insurance}', [MarketingInsuranceController::class, 'show'])->name('insurances.show');
    Route::get('/insurances/{insurance}/edit', [MarketingInsuranceController::class, 'edit'])->name('insurances.edit');
    Route::put('/insurances/{insurance}', [MarketingInsuranceController::class, 'update'])->name('insurances.update');
});

// Kasir Routes
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::resource('transactions', TransactionController::class);
    Route::post('transactions/{transaction}/pay', [TransactionController::class, 'pay'])->name('transactions.pay');
    Route::get('transactions/{transaction}/print', [TransactionController::class, 'printReceipt'])->name('transactions.print');
    Route::post('transactions/check-voucher', [TransactionController::class, 'checkVoucher'])->name('transactions.check-voucher');
    
    // Test voucher page
    Route::get('test-voucher', function () {
        return view('test-voucher');
    })->name('test-voucher');
});

require __DIR__.'/auth.php';
