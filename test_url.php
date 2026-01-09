<?php
// This file simulates what should be rendered in the blade template

// For admin in COMPANY_B
$user_id = 2;
$company_code = 'COMPANY_B';
$is_superadmin = false;

if ($is_superadmin) {
    // SuperAdmin route generation
    // route('superadmin.users.destroy', $user)
    // Expected: /superadmin/users/2
    $delete_url = "/superadmin/users/{$user_id}";
} else {
    // Admin route generation  
    // route('admin.users.destroy', ['company_code' => $company_code, 'user' => $user])
    // Expected: /COMPANY_B/admin/users/2
    $delete_url = "/{$company_code}/admin/users/{$user_id}";
}

echo "Delete URL should be: {$delete_url}\n";

// Test fetch
echo "\nFetch request:\n";
echo "DELETE {$delete_url}\n";
echo "Headers:\n";
echo "  X-CSRF-TOKEN: <token>\n";
echo "  Accept: application/json\n";
echo "  Content-Type: application/json\n";
