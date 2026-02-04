<?php

echo "========================================\n";
echo "Detailed API Testing (After Successful Auth)\n";
echo "========================================\n\n";

$baseUrl = 'https://recruitment.rsdeltasurya.com/api/v1';

// Read credentials from .env file
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

echo "Using credentials from .env:\n";
echo "Email: $email\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

// Step 1: Authentication
echo str_repeat('=', 60) . "\n";
echo "STEP 1: Authentication\n";
echo str_repeat('=', 60) . "\n";

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
$curlError = curl_error($ch);
curl_close($ch);

echo "Status: $httpCode\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
    exit(1);
}

$authResponse = json_decode($response, true);

if ($httpCode !== 200 || !isset($authResponse['access_token'])) {
    echo "❌ Authentication failed!\n";
    echo "Response:\n";
    echo json_encode($authResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}

$token = $authResponse['access_token'];
echo "✅ Authentication successful!\n";
echo "Token: " . substr($token, 0, 40) . "...\n";
echo "Expires in: " . $authResponse['expires_in'] . " seconds\n\n";

// Step 2: Get Insurances
echo str_repeat('=', 60) . "\n";
echo "STEP 2: Get Insurances\n";
echo str_repeat('=', 60) . "\n";

$ch = curl_init($baseUrl . '/insurances');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);

curl_close($ch);

echo "Request: GET {$baseUrl}/insurances\n";
echo "Status: $httpCode\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
}

if ($httpCode === 200) {
    $insurances = json_decode($response, true);
    echo "✅ Success!\n";
    echo "Response Type: " . gettype($insurances) . "\n";
    
    echo "\nFull Response Structure:\n";
    echo json_encode($insurances, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (is_array($insurances)) {
        // Check if it's a paginated response
        if (isset($insurances['data'])) {
            echo "\n✓ Paginated response detected\n";
            $items = $insurances['data'];
            echo "Count: " . count($items) . " items\n";
            
            if (count($items) > 0) {
                echo "\nFirst item:\n";
                echo json_encode($items[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "Count: " . count($insurances) . " items\n";
            
            if (count($insurances) > 0) {
                echo "\nFirst item:\n";
                echo json_encode($insurances[0] ?? $insurances, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
    }
} else {
    echo "❌ Failed!\n";
    echo "Response:\n";
    echo $response . "\n\n";
    
    echo "Verbose cURL info:\n";
    echo $verboseLog . "\n";
}

echo "\n";

// Step 3: Get Procedures
echo str_repeat('=', 60) . "\n";
echo "STEP 3: Get Procedures (Tindakan Medis)\n";
echo str_repeat('=', 60) . "\n";

$ch = curl_init($baseUrl . '/procedures');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Request: GET {$baseUrl}/procedures\n";
echo "Status: $httpCode\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
}

$procedureId = null;

if ($httpCode === 200) {
    $procedures = json_decode($response, true);
    echo "✅ Success!\n";
    echo "Response Type: " . gettype($procedures) . "\n";
    
    echo "\nFull Response Structure:\n";
    echo json_encode($procedures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (is_array($procedures)) {
        // Check if it's a paginated response
        if (isset($procedures['data'])) {
            echo "\n✓ Paginated response detected\n";
            $items = $procedures['data'];
            echo "Count: " . count($items) . " items\n";
            
            if (count($items) > 0) {
                echo "\nFirst item:\n";
                echo json_encode($items[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                $procedureId = $items[0]['id'] ?? null;
            }
        } else {
            echo "Count: " . count($procedures) . " items\n";
            
            if (count($procedures) > 0) {
                echo "\nFirst item:\n";
                echo json_encode($procedures[0] ?? $procedures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                $procedureId = ($procedures[0]['id'] ?? null) ?: ($procedures['id'] ?? null);
            }
        }
    }
} else {
    echo "❌ Failed!\n";
    echo "Response:\n";
    echo $response . "\n";
}

echo "\n";

// Step 4: Get Procedure Prices
if ($procedureId) {
    echo str_repeat('=', 60) . "\n";
    echo "STEP 4: Get Procedure Prices\n";
    echo str_repeat('=', 60) . "\n";

    $priceUrl = $baseUrl . '/procedures/' . $procedureId . '/prices';
    
    $ch = curl_init($priceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "Request: GET {$priceUrl}\n";
    echo "Status: $httpCode\n";

    if ($curlError) {
        echo "cURL Error: $curlError\n";
    }

    if ($httpCode === 200) {
        $prices = json_decode($response, true);
        echo "✅ Success!\n";
        echo "Response Type: " . gettype($prices) . "\n";
        
        if (is_array($prices)) {
            echo "Count: " . count($prices) . " items\n\n";
            
            if (count($prices) > 0) {
                echo "First item:\n";
                echo json_encode($prices[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "Full response:\n";
            echo json_encode($prices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ Failed!\n";
        echo "Response:\n";
        echo $response . "\n";
    }
} else {
    echo str_repeat('=', 60) . "\n";
    echo "STEP 4: Get Procedure Prices\n";
    echo str_repeat('=', 60) . "\n";
    echo "⚠️ Skipped - No procedure ID from previous step\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Testing completed!\n";
echo str_repeat('=', 60) . "\n";
