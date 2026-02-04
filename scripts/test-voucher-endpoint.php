<?php
/**
 * Test Voucher Check Endpoint
 * Run: php test-voucher-endpoint.php
 */

// Simulate POST request to check voucher
$url = 'http://127.0.0.1:8000/kasir/transactions/check-voucher';

$data = [
    'code' => 'NEWYEAR2026',
    'subtotal' => 100000,
    'insurance_id' => null
];

// Get CSRF token first
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/kasir/transactions/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
$html = curl_exec($ch);
curl_close($ch);

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="(.+?)"/', $html, $matches);
$csrfToken = $matches[1] ?? '';

echo "CSRF Token: " . substr($csrfToken, 0, 20) . "...\n\n";

// Make POST request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ' . $csrfToken,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response:\n";
echo $response . "\n";

// Cleanup
if (file_exists('cookie.txt')) {
    unlink('cookie.txt');
}
