<?php

echo "========================================\n";
echo "API Authentication Error Testing\n";
echo "========================================\n\n";

$baseUrl = 'https://recruitment.rsdeltasurya.com/api/v1/auth';

// Test scenarios
$scenarios = [
    [
        'name' => 'Test 1: Email tidak terdaftar',
        'email' => 'random@example.com',
        'password' => '081234567890'
    ],
    [
        'name' => 'Test 2: Email valid, password salah',
        'email' => 'arfi.afianto@rsdeltasurya.com',
        'password' => 'wrongpassword'
    ],
    [
        'name' => 'Test 3: Email dan password dari contoh (jika valid)',
        'email' => 'arfi.afianto@rsdeltasurya.com',
        'password' => '081234567890'
    ],
];

foreach ($scenarios as $scenario) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo $scenario['name'] . "\n";
    echo str_repeat('=', 60) . "\n";
    
    $data = [
        'email' => $scenario['email'],
        'password' => $scenario['password']
    ];

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "Email: {$data['email']}\n";
    echo "Password: {$data['password']}\n";
    echo "Status Code: $httpCode\n";
    
    if ($error) {
        echo "cURL Error: $error\n";
    }
    
    echo "\nResponse:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // Parse error message
        if (isset($responseData['errors'])) {
            echo "\nParsed Errors:\n";
            foreach ($responseData['errors'] as $field => $messages) {
                echo "  - $field: " . implode(', ', $messages) . "\n";
            }
        }
    } else {
        echo $response . "\n";
    }
    
    sleep(1); // Delay to avoid rate limiting
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Testing completed!\n";
echo str_repeat('=', 60) . "\n";

