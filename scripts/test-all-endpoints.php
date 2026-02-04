<?php

echo "========================================\n";
echo "API Endpoints Testing\n";
echo "========================================\n\n";

$baseUrl = 'https://recruitment.rsdeltasurya.com/api/v1';

// Step 1: Authentication
echo "Step 1: Authentication (POST)\n";
echo str_repeat('-', 60) . "\n";

$authData = [
    'email' => 'arfi.afianto@rsdeltasurya.com',
    'password' => '081234567890'
];

$ch = curl_init($baseUrl . '/auth');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($authData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Request: POST {$baseUrl}/auth\n";
echo "Status: $httpCode\n";

$authResponse = json_decode($response, true);
$token = $authResponse['access_token'] ?? null;

if ($token) {
    echo "✅ Authentication successful!\n";
    echo "Token: " . substr($token, 0, 30) . "...\n";
    echo "Expires in: " . ($authResponse['expires_in'] ?? 'N/A') . " seconds\n\n";
} else {
    echo "❌ Authentication failed!\n";
    echo "Response: " . json_encode($authResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}

// Step 2: Get Insurances
echo "\nStep 2: Get Insurances (GET)\n";
echo str_repeat('-', 60) . "\n";

$ch = curl_init($baseUrl . '/insurances');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Request: GET {$baseUrl}/insurances\n";
echo "Status: $httpCode\n";

if ($httpCode === 200) {
    $insurances = json_decode($response, true);
    echo "✅ Insurances retrieved successfully!\n";
    echo "Count: " . (is_array($insurances) ? count($insurances) : 'N/A') . " items\n";
    
    if (is_array($insurances) && count($insurances) > 0) {
        echo "\nSample data (first item):\n";
        echo json_encode($insurances[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "❌ Failed to get insurances!\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

// Step 3: Get Procedures
echo "\nStep 3: Get Procedures/Medical Services (GET)\n";
echo str_repeat('-', 60) . "\n";

$ch = curl_init($baseUrl . '/procedures');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Request: GET {$baseUrl}/procedures\n";
echo "Status: $httpCode\n";

$procedureId = null;

if ($httpCode === 200) {
    $procedures = json_decode($response, true);
    echo "✅ Procedures retrieved successfully!\n";
    echo "Count: " . (is_array($procedures) ? count($procedures) : 'N/A') . " items\n";
    
    if (is_array($procedures) && count($procedures) > 0) {
        echo "\nSample data (first item):\n";
        echo json_encode($procedures[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // Get procedure ID for next test
        $procedureId = $procedures[0]['id'] ?? null;
    }
} else {
    echo "❌ Failed to get procedures!\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

// Step 4: Get Procedure Prices
if ($procedureId) {
    echo "\nStep 4: Get Procedure Prices (GET)\n";
    echo str_repeat('-', 60) . "\n";

    $ch = curl_init($baseUrl . '/procedures/' . $procedureId . '/prices');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Request: GET {$baseUrl}/procedures/{$procedureId}/prices\n";
    echo "Status: $httpCode\n";

    if ($httpCode === 200) {
        $prices = json_decode($response, true);
        echo "✅ Procedure prices retrieved successfully!\n";
        echo "Count: " . (is_array($prices) ? count($prices) : 'N/A') . " items\n";
        
        if (is_array($prices) && count($prices) > 0) {
            echo "\nSample data (first item):\n";
            echo json_encode($prices[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "\nFull response:\n";
            echo json_encode($prices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ Failed to get procedure prices!\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
} else {
    echo "\nStep 4: Get Procedure Prices (GET)\n";
    echo str_repeat('-', 60) . "\n";
    echo "⚠️ Skipped - No procedure ID available\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Testing completed!\n";
echo str_repeat('=', 60) . "\n";

echo "\n📝 Summary:\n";
echo "- Authentication: POST (with email & password)\n";
echo "- Insurances: GET (with Bearer token)\n";
echo "- Procedures: GET (with Bearer token)\n";
echo "- Procedure Prices: GET (with Bearer token, requires procedure ID)\n";
