<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Counter Endpoint ===" . PHP_EOL;

// Test 1: Direct endpoint access without authentication
echo "Test 1: Direct endpoint access (should fail with 403 or redirect)" . PHP_EOL;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/default/counter/data');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode" . PHP_EOL;
echo "Response Headers:" . PHP_EOL;
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
echo substr($response, 0, 500) . PHP_EOL;

echo PHP_EOL . "Test 2: Check if login page is accessible" . PHP_EOL;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login page HTTP Code: $httpCode" . PHP_EOL;
if ($httpCode == 200) {
    echo "Login page is accessible" . PHP_EOL;
} else {
    echo "Login page is not accessible" . PHP_EOL;
}

echo PHP_EOL . "Test 3: Check organization route" . PHP_EOL;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/default/kiosk');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Kiosk page HTTP Code: $httpCode" . PHP_EOL;
if ($httpCode == 200) {
    echo "Organization routing is working" . PHP_EOL;
} else {
    echo "Organization routing has issues" . PHP_EOL;
}