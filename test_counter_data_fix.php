<?php
/**
 * Test script to verify the counter/data endpoint fix
 * This script tests the 403 error fix for the counter data endpoint
 */

// Test the counter/data endpoint
$baseUrl = 'http://localhost:8000'; // Change this to your actual URL
$testUrl = $baseUrl . '/default/counter/data?counter_id=3';

echo "Testing Counter Data Endpoint Fix\n";
echo "=================================\n\n";

echo "Test URL: $testUrl\n\n";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'User-Agent: Counter-Data-Test/1.0'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Results:\n";
echo "--------\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "HTTP Status Code: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "✅ SUCCESS: Endpoint returned 200 OK\n";
        echo "Response Data:\n";
        
        $jsonData = json_decode($response, true);
        if ($jsonData) {
            echo json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
            
            // Check if it's a successful response
            if (isset($jsonData['success']) && $jsonData['success'] === true) {
                echo "✅ Response indicates success\n";
            } else {
                echo "⚠️  Response success flag not set or false\n";
            }
            
            // Check for expected data structure
            $expectedKeys = ['online_status', 'current_queue', 'waiting_queues', 'online_counters'];
            $hasExpectedStructure = true;
            foreach ($expectedKeys as $key) {
                if (!array_key_exists($key, $jsonData)) {
                    echo "⚠️  Missing expected key: $key\n";
                    $hasExpectedStructure = false;
                }
            }
            
            if ($hasExpectedStructure) {
                echo "✅ Response has expected data structure\n";
            }
            
        } else {
            echo "⚠️  Response is not valid JSON\n";
            echo "Raw response: " . substr($response, 0, 500) . "\n";
        }
        
    } elseif ($httpCode === 403) {
        echo "❌ FAILED: Still getting 403 Forbidden error\n";
        echo "Response: $response\n";
        echo "\nThis indicates the fix did not work. Check:\n";
        echo "1. Middleware is properly applied\n";
        echo "2. Route has 'allow.public' middleware\n";
        echo "3. Organization 'default' exists in database\n";
        
    } elseif ($httpCode === 404) {
        echo "❌ FAILED: 404 Not Found - Route or organization not found\n";
        echo "Response: $response\n";
        
    } else {
        echo "⚠️  Unexpected HTTP status code: $httpCode\n";
        echo "Response: $response\n";
    }
}

echo "\n";
echo "Additional Tests:\n";
echo "-----------------\n";

// Test without counter_id parameter
$testUrl2 = $baseUrl . '/default/counter/data';
echo "Testing without counter_id parameter: $testUrl2\n";

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $testUrl2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "HTTP Status Code: $httpCode2\n";
if ($httpCode2 === 200) {
    echo "✅ SUCCESS: Endpoint works without counter_id parameter\n";
} else {
    echo "⚠️  Status: $httpCode2 - This might be expected behavior\n";
}

echo "\nTest completed.\n";
echo "\nIf you're still getting 403 errors, check the Laravel logs at:\n";
echo "storage/logs/laravel.log\n";
echo "\nLook for entries with 'Public route accessed by authenticated user' or '403 Unauthorized organization access attempt'\n";
?>