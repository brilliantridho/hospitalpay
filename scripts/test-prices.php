<?php

echo "========================================\n";
echo "Test Get Procedure Prices\n";
echo "========================================\n\n";

$baseUrl = 'https://recruitment.rsdeltasurya.com/api/v1';

// Read credentials from .env
$envFile = __DIR__ . '/.env';
$env = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

$email = $env['EXTERNAL_API_EMAIL'] ?? '';
$password = $env['EXTERNAL_API_PASSWORD'] ?? '';

// Step 1: Authenticate
echo "Authenticating...\n";
$ch = curl_init($baseUrl . '/auth');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $email,
    'password' => $password
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Authentication failed!\n";
    exit(1);
}

$authResponse = json_decode($response, true);
$token = $authResponse['access_token'];
echo "✅ Authenticated!\n\n";

// Test with the procedure ID from documentation
$procedureId = '019c125d-a359-7156-99cd-e188ff48294c'; // CT Scan abdomen

echo "Testing with Procedure ID: $procedureId\n";
echo "Procedure: CT Scan abdomen / perut\n";
echo str_repeat('-', 60) . "\n\n";

$priceUrl = $baseUrl . '/procedures/' . $procedureId . '/prices';

$ch = curl_init($priceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Request: GET $priceUrl\n";
echo "Status: $httpCode\n\n";

if ($httpCode === 200) {
    $prices = json_decode($response, true);
    echo "✅ Success!\n\n";
    echo "Full Response:\n";
    echo json_encode($prices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Check structure
    if (isset($prices['prices'])) {
        echo "Structure: Wrapped in 'prices' key\n";
        echo "Count: " . count($prices['prices']) . " price items\n\n";
        
        if (count($prices['prices']) > 0) {
            echo "Sample (first item):\n";
            echo json_encode($prices['prices'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } elseif (is_array($prices) && count($prices) > 0) {
        echo "Structure: Direct array\n";
        echo "Count: " . count($prices) . " price items\n\n";
        echo "Sample (first item):\n";
        echo json_encode($prices[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "❌ Failed!\n";
    echo "Response:\n";
    echo $response . "\n";
}
