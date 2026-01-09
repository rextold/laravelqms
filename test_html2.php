<?php
$url = 'http://127.0.0.1:8000/COMPANY_B/admin/users';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = @file_get_contents($url, false, $context);
$headers = $http_response_header ?? [];

echo "Status: " . ($headers[0] ?? 'No response') . "\n";
echo "Response length: " . strlen($response) . " bytes\n";

if (strpos($headers[0] ?? '', '302') !== false || strpos($headers[0] ?? '', '301') !== false) {
    echo "Redirected (need authentication)\n";
    foreach ($headers as $header) {
        if (stripos($header, 'location') !== false) {
            echo "Location: $header\n";
        }
    }
} elseif (strpos($response, 'data-delete-url') !== false) {
    // Extract the first delete URL
    if (preg_match('/data-delete-url="([^"]+)"/', $response, $matches)) {
        echo "First delete URL: " . $matches[1] . "\n";
    }
} else {
    echo "No delete URLs in response\n";
    // Show first 500 chars to debug
    echo "Response preview: " . substr($response, 0, 500) . "\n";
}
