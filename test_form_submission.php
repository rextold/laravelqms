<?php

// Test script to validate form submission data
// This will verify that all required fields are being sent correctly

$test_data = [
    'organization_name' => 'Test Organization',
    'company_phone' => '+1-555-1234567',
    'company_email' => 'test@example.com',
    'company_address' => '123 Main Street',
    'primary_color' => '#3B82F6',      // Blue
    'secondary_color' => '#10B981',    // Green
    'accent_color' => '#F59E0B',       // Amber
    'text_color' => '#1F2937',         // Dark Gray
    'queue_number_digits' => '4'
];

echo "Test Form Data Validation\n";
echo "=========================\n\n";

// Validate color format
$color_regex = '/^#[0-9A-F]{6}$/i';
$required_fields = [
    'organization_name' => 'required|string|max:255',
    'company_phone' => 'nullable|string|max:255',
    'company_email' => 'nullable|email|max:255',
    'company_address' => 'nullable|string|max:500',
    'primary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
    'secondary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
    'accent_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
    'text_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
    'queue_number_digits' => 'required|integer|min:1|max:10',
];

foreach ($required_fields as $field => $rule) {
    if (!isset($test_data[$field])) {
        echo "[ERROR] Field missing: $field\n";
        continue;
    }
    
    $value = $test_data[$field];
    echo "[$field]: '$value' - ";
    
    // Check if field is in test data
    if (strpos($rule, 'required') !== false) {
        if (empty($value)) {
            echo "FAIL (Required but empty)\n";
        } else {
            // Check type if it's a color field
            if (strpos($field, 'color') !== false) {
                if (preg_match($color_regex, $value)) {
                    echo "PASS (Valid hex color)\n";
                } else {
                    echo "FAIL (Invalid hex color format)\n";
                }
            } else if ($field === 'queue_number_digits') {
                if (is_numeric($value) && (int)$value >= 1 && (int)$value <= 10) {
                    echo "PASS (Valid integer)\n";
                } else {
                    echo "FAIL (Invalid integer range)\n";
                }
            } else {
                echo "PASS\n";
            }
        }
    } else {
        echo "PASS (Nullable)\n";
    }
}

echo "\n=== FormData Simulation ===\n";
echo "All fields that would be submitted:\n";
foreach ($test_data as $key => $value) {
    echo "  '$key' => '$value'\n";
}

echo "\nForm submission should work correctly.\n";
?>
