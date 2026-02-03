<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Database Check\n";
echo "========================================\n\n";

echo "📋 INSURANCES IN DATABASE:\n";
echo "Total: " . App\Models\Insurance::count() . " records\n\n";

$insurances = App\Models\Insurance::limit(3)->get();
foreach ($insurances as $ins) {
    echo "ID: {$ins->id}\n";
    echo "Code: {$ins->code}\n";
    echo "Name: {$ins->name}\n";
    echo "Discount %: {$ins->discount_percentage}\n";
    echo "Description: " . ($ins->description ?? '(null)') . "\n";
    echo str_repeat('-', 40) . "\n";
}

echo "\n💉 MEDICAL SERVICES IN DATABASE:\n";
echo "Total: " . App\Models\MedicalService::count() . " records\n\n";

$services = App\Models\MedicalService::limit(3)->get();
foreach ($services as $ms) {
    echo "ID: {$ms->id}\n";
    echo "Code: {$ms->code}\n";
    echo "Name: {$ms->name}\n";
    echo "Price (DB): Rp " . number_format($ms->price, 0, ',', '.') . "\n";
    echo "Category: " . ($ms->category ?? '(null)') . "\n";
    echo "Description: " . ($ms->description ?? '(null)') . "\n";
    
    // Try to get current price from API
    try {
        $currentPrice = $ms->getCurrentPrice();
        echo "Price (API Real-time): Rp " . number_format($currentPrice, 0, ',', '.') . "\n";
    } catch (Exception $e) {
        echo "Price (API Real-time): Error - " . $e->getMessage() . "\n";
    }
    
    echo str_repeat('-', 40) . "\n";
}

echo "\n📊 SUMMARY:\n";
echo "- API Response untuk /insurances: Hanya id dan name\n";
echo "- API Response untuk /procedures: Hanya id dan name\n";
echo "- Harga tindakan: Tersedia di endpoint terpisah /procedures/{id}/prices\n";
echo "- Harga asuransi/diskon: TIDAK ada di API\n";
