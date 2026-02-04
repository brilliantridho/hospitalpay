<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Test Validasi Harga untuk Transaksi\n";
echo "========================================\n\n";

// Test 1: Cek semua medical services punya harga
echo "📋 Test 1: Validasi Harga Tindakan Medis\n";
echo str_repeat('-', 60) . "\n";

$services = App\Models\MedicalService::all();
$servicesWithoutPrice = [];
$servicesWithPrice = [];

foreach ($services as $service) {
    try {
        $price = $service->getCurrentPrice();
        
        if (!$price || $price <= 0) {
            $servicesWithoutPrice[] = [
                'name' => $service->name,
                'code' => $service->code,
                'db_price' => $service->price
            ];
        } else {
            $servicesWithPrice[] = [
                'name' => $service->name,
                'code' => $service->code,
                'price' => $price
            ];
        }
    } catch (Exception $e) {
        $servicesWithoutPrice[] = [
            'name' => $service->name,
            'code' => $service->code,
            'error' => $e->getMessage()
        ];
    }
}

echo "✅ Tindakan dengan harga valid: " . count($servicesWithPrice) . "\n";
foreach (array_slice($servicesWithPrice, 0, 5) as $s) {
    echo "   - {$s['name']}: Rp " . number_format($s['price'], 0, ',', '.') . "\n";
}
if (count($servicesWithPrice) > 5) {
    echo "   ... dan " . (count($servicesWithPrice) - 5) . " lainnya\n";
}

echo "\n";

if (count($servicesWithoutPrice) > 0) {
    echo "❌ Tindakan TANPA harga valid: " . count($servicesWithoutPrice) . "\n";
    foreach ($servicesWithoutPrice as $s) {
        echo "   - {$s['name']} (code: {$s['code']})\n";
        if (isset($s['error'])) {
            echo "     Error: {$s['error']}\n";
        } else {
            echo "     DB Price: Rp " . number_format($s['db_price'], 0, ',', '.') . "\n";
        }
    }
    echo "\n⚠️ PERINGATAN: Transaksi dengan tindakan di atas akan DITOLAK!\n";
} else {
    echo "✅ Semua tindakan memiliki harga valid!\n";
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// Test 2: Cek asuransi punya diskon atau voucher
echo "📋 Test 2: Validasi Diskon Asuransi\n";
echo str_repeat('-', 60) . "\n";

$insurances = App\Models\Insurance::all();
$insurancesWithDiscount = [];
$insurancesWithoutDiscount = [];

foreach ($insurances as $insurance) {
    $voucher = App\Models\Voucher::where('insurance_id', $insurance->id)
        ->where('is_active', true)
        ->first();
    
    if ($insurance->discount_percentage > 0) {
        $insurancesWithDiscount[] = [
            'name' => $insurance->name,
            'discount' => $insurance->discount_percentage,
            'type' => 'database'
        ];
    } elseif ($voucher) {
        $insurancesWithDiscount[] = [
            'name' => $insurance->name,
            'discount' => $voucher->discount_percentage,
            'type' => 'voucher',
            'code' => $voucher->code
        ];
    } else {
        $insurancesWithoutDiscount[] = [
            'name' => $insurance->name
        ];
    }
}

echo "✅ Asuransi dengan diskon/voucher: " . count($insurancesWithDiscount) . "\n";
foreach ($insurancesWithDiscount as $i) {
    if ($i['type'] === 'voucher') {
        echo "   - {$i['name']}: {$i['discount']}% (voucher: {$i['code']})\n";
    } else {
        echo "   - {$i['name']}: {$i['discount']}% (database)\n";
    }
}

echo "\n";

if (count($insurancesWithoutDiscount) > 0) {
    echo "⚠️ Asuransi TANPA diskon: " . count($insurancesWithoutDiscount) . "\n";
    foreach ($insurancesWithoutDiscount as $i) {
        echo "   - {$i['name']}\n";
    }
    echo "\n⚠️ CATATAN: Transaksi tetap bisa dibuat, tapi tidak ada diskon.\n";
} else {
    echo "✅ Semua asuransi memiliki diskon!\n";
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// Test 3: Simulasi validasi transaksi
echo "📋 Test 3: Simulasi Validasi Transaksi\n";
echo str_repeat('-', 60) . "\n";

$testService = App\Models\MedicalService::first();
if ($testService) {
    echo "Mencoba validasi untuk tindakan: {$testService->name}\n";
    
    try {
        $price = $testService->getCurrentPrice();
        
        if (!$price || $price <= 0) {
            echo "❌ VALIDASI GAGAL: Harga tidak tersedia atau = 0\n";
            echo "   Transaksi akan DITOLAK dengan error:\n";
            echo "   \"Harga untuk tindakan '{$testService->name}' tidak tersedia.\"\n";
        } else {
            echo "✅ VALIDASI BERHASIL: Harga tersedia\n";
            echo "   Harga: Rp " . number_format($price, 0, ',', '.') . "\n";
            echo "   Transaksi dapat dilanjutkan.\n";
        }
    } catch (Exception $e) {
        echo "❌ VALIDASI GAGAL: Error saat mengambil harga\n";
        echo "   Error: {$e->getMessage()}\n";
        echo "   Transaksi akan DITOLAK.\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// Summary
echo "📊 RINGKASAN\n";
echo str_repeat('-', 60) . "\n";
echo "Total Tindakan Medis: " . count($services) . "\n";
echo "- Dengan harga valid: " . count($servicesWithPrice) . " ✅\n";
echo "- Tanpa harga valid: " . count($servicesWithoutPrice) . " " . (count($servicesWithoutPrice) > 0 ? "❌" : "✅") . "\n";
echo "\n";
echo "Total Asuransi: " . count($insurances) . "\n";
echo "- Dengan diskon/voucher: " . count($insurancesWithDiscount) . " ✅\n";
echo "- Tanpa diskon: " . count($insurancesWithoutDiscount) . " ⚠️\n";
echo "\n";

if (count($servicesWithoutPrice) > 0) {
    echo "⚠️ ACTION REQUIRED:\n";
    echo "   Jalankan: php artisan external:sync --procedures\n";
    echo "   Atau set harga manual di database untuk tindakan tanpa harga.\n";
} else {
    echo "✅ SISTEM SIAP UNTUK TRANSAKSI!\n";
    echo "   Semua tindakan memiliki harga valid.\n";
}
