<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Database Prices Detail\n";
echo "========================================\n\n";

$services = DB::table('medical_services')
    ->whereNotNull('code')
    ->orderBy('price_updated_at', 'desc')
    ->limit(10)
    ->get();

echo "📊 Medical Services dengan Harga dari API (10 terakhir diupdate):\n";
echo str_repeat('=', 80) . "\n";

foreach ($services as $service) {
    echo "ID: {$service->id}\n";
    echo "Code: {$service->code}\n";
    echo "Name: {$service->name}\n";
    echo "Price: Rp " . number_format($service->price, 0, ',', '.') . "\n";
    echo "Price Source: " . ($service->price_source ?? 'N/A') . "\n";
    echo "Last Updated: " . ($service->price_updated_at ?? 'Never') . "\n";
    echo str_repeat('-', 80) . "\n";
}

// Statistics
$apiPrices = DB::table('medical_services')
    ->where('price_source', 'api')
    ->count();

$manualPrices = DB::table('medical_services')
    ->whereNull('price_source')
    ->orWhere('price_source', '!=', 'api')
    ->count();

$totalServices = DB::table('medical_services')->count();

echo "\n📊 STATISTIK:\n";
echo str_repeat('=', 80) . "\n";
echo "Total Tindakan: {$totalServices}\n";
echo "Harga dari API: {$apiPrices} (" . round(($apiPrices / $totalServices) * 100, 1) . "%)\n";
echo "Harga Manual/Seeder: {$manualPrices} (" . round(($manualPrices / $totalServices) * 100, 1) . "%)\n";
echo "\n";

$recentlyUpdated = DB::table('medical_services')
    ->whereNotNull('price_updated_at')
    ->where('price_updated_at', '>=', now()->subHours(24))
    ->count();

echo "✅ Harga diupdate dalam 24 jam terakhir: {$recentlyUpdated}\n";

if ($recentlyUpdated < $totalServices) {
    $needUpdate = $totalServices - $recentlyUpdated;
    echo "⚠️  Tindakan yang perlu update harga: {$needUpdate}\n";
    echo "   Jalankan: php artisan external:sync --prices\n";
} else {
    echo "✅ Semua harga up-to-date!\n";
}
