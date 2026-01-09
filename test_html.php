<?php
// Test the rendered HTML to verify data-delete-url is correct
$html = file_get_contents('http://127.0.0.1:8000/COMPANY_B/admin/users');

if (preg_match('/data-delete-url="([^"]+)"/', $html, $matches)) {
    echo "First delete URL found:\n";
    echo $matches[1] . "\n";
} else {
    echo "No delete URL found in HTML\n";
}

// Also check if there are any data-user-id attributes
if (preg_match_all('/data-user-id="(\d+)"/', $html, $matches)) {
    echo "\nUser IDs found: ";
    print_r($matches[1]);
}
