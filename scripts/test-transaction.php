<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Transaction Creation ===\n\n";

// Check medical services
$servicesCount = App\Models\MedicalService::count();
echo "Medical Services in database: $servicesCount\n";

if ($servicesCount > 0) {
    $service = App\Models\MedicalService::first();
    echo "First service: {$service->name}\n";
    echo "Service code: {$service->code}\n";
    echo "Service price: {$service->price}\n";
    
    try {
        $price = $service->getCurrentPrice();
        echo "Current price: $price\n";
    } catch (\Exception $e) {
        echo "ERROR getting price: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️ WARNING: No medical services found!\n";
}

echo "\n";

// Check insurances
$insurancesCount = App\Models\Insurance::count();
echo "Insurances in database: $insurancesCount\n";

if ($insurancesCount > 0) {
    $insurance = App\Models\Insurance::first();
    echo "First insurance: {$insurance->name}\n";
    echo "Discount: {$insurance->discount_percentage}%\n";
}

echo "\n";

// Check recent transactions
$transactionsCount = App\Models\Transaction::count();
echo "Total transactions: $transactionsCount\n";

if ($transactionsCount > 0) {
    $lastTransaction = App\Models\Transaction::latest()->first();
    echo "Last transaction: {$lastTransaction->transaction_code}\n";
    echo "Patient: {$lastTransaction->patient_name}\n";
    echo "Status: {$lastTransaction->payment_status}\n";
}

echo "\n=== Test Complete ===\n";
