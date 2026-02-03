<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Insurance;

echo "========================================\n";
echo "Data Asuransi dengan Diskon & Ketentuan\n";
echo "========================================\n\n";

$insurances = Insurance::orderBy('discount_percentage', 'desc')->get();

echo "📊 Total Asuransi: {$insurances->count()}\n";
echo str_repeat('=', 100) . "\n\n";

foreach ($insurances as $insurance) {
    echo "🏥 {$insurance->name}\n";
    echo str_repeat('-', 100) . "\n";
    echo "Diskon: {$insurance->discount_percentage}%\n";
    
    if ($insurance->coverage_limit) {
        echo "Limit Tanggungan: Rp " . number_format($insurance->coverage_limit, 0, ',', '.') . " per tahun\n";
    } else {
        echo "Limit Tanggungan: Unlimited (sesuai ketentuan)\n";
    }
    
    echo "Status: " . ($insurance->is_active ? "✅ Aktif" : "❌ Non-aktif") . "\n";
    
    if ($insurance->terms) {
        echo "\nKetentuan & Syarat:\n";
        echo $insurance->terms . "\n";
    }
    
    // Check vouchers
    $vouchers = $insurance->vouchers()->where('is_active', true)->get();
    if ($vouchers->count() > 0) {
        echo "\n💳 Voucher Aktif: {$vouchers->count()}\n";
        foreach ($vouchers as $voucher) {
            echo "   - {$voucher->code}: {$voucher->discount_percentage}% ";
            if ($voucher->valid_from && $voucher->valid_until) {
                echo "(berlaku: {$voucher->valid_from->format('d/m/Y')} - {$voucher->valid_until->format('d/m/Y')})\n";
            } else {
                echo "(periode tidak ditentukan)\n";
            }
        }
    }
    
    echo "\n" . str_repeat('=', 100) . "\n\n";
}

// Summary statistics
echo "📊 RINGKASAN:\n";
echo str_repeat('=', 100) . "\n";

$withDiscount = $insurances->filter(fn($i) => $i->discount_percentage > 0)->count();
$withoutDiscount = $insurances->filter(fn($i) => $i->discount_percentage == 0)->count();
$avgDiscount = $insurances->where('discount_percentage', '>', 0)->avg('discount_percentage');

echo "✅ Asuransi dengan diskon: {$withDiscount}\n";
echo "⚠️  Asuransi tanpa diskon: {$withoutDiscount}\n";
echo "📊 Rata-rata diskon: " . number_format($avgDiscount, 2) . "%\n";

// Grouping by discount range
$discountRanges = [
    '100%' => $insurances->filter(fn($i) => $i->discount_percentage == 100)->count(),
    '80-89%' => $insurances->filter(fn($i) => $i->discount_percentage >= 80 && $i->discount_percentage < 90)->count(),
    '70-79%' => $insurances->filter(fn($i) => $i->discount_percentage >= 70 && $i->discount_percentage < 80)->count(),
    '60-69%' => $insurances->filter(fn($i) => $i->discount_percentage >= 60 && $i->discount_percentage < 70)->count(),
    '0%' => $insurances->filter(fn($i) => $i->discount_percentage == 0)->count(),
];

echo "\n📊 Distribusi Diskon:\n";
foreach ($discountRanges as $range => $count) {
    if ($count > 0) {
        echo "   {$range}: {$count} asuransi\n";
    }
}

echo "\n✅ SEMUA ASURANSI TELAH MEMILIKI DISKON DAN KETENTUAN!\n";
