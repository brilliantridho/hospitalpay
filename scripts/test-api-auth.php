<?php

// Test script untuk autentikasi API eksternal
// Jalankan dengan: php test-api-auth.php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = $_ENV['EXTERNAL_API_BASE_URL'] ?? 'https://recruitment.rsdeltasurya.com/api/v1';
$email = $_ENV['EXTERNAL_API_EMAIL'] ?? '';
$password = $_ENV['EXTERNAL_API_PASSWORD'] ?? '';

echo "=================================\n";
echo "API Authentication Test\n";
echo "=================================\n\n";

echo "Configuration:\n";
echo "- Base URL: $baseUrl\n";
echo "- Email: $email\n";
echo "- Password: " . str_repeat('*', strlen($password)) . "\n\n";

if (empty($email) || empty($password)) {
    echo "❌ ERROR: Email atau Password tidak diset di .env\n";
    exit(1);
}

echo "Testing authentication...\n\n";

try {
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
    ])->post($baseUrl . '/auth', [
        'email' => $email,
        'password' => $password,
    ]);

    echo "Status Code: " . $response->status() . "\n\n";

    if ($response->successful()) {
        $data = $response->json();
        
        echo "✅ Authentication Successful!\n\n";
        echo "Response:\n";
        echo "- Token Type: " . ($data['token_type'] ?? 'N/A') . "\n";
        echo "- Expires In: " . ($data['expires_in'] ?? 'N/A') . " seconds\n";
        echo "- Access Token: " . substr($data['access_token'] ?? '', 0, 50) . "...\n\n";
    } else {
        echo "❌ Authentication Failed!\n\n";
        echo "Response:\n";
        echo $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
