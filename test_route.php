<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Set up URL context
$_SERVER['REQUEST_URI'] = '/COMPANY_B/admin/users';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8000';

$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Test route generation
echo "Testing route generation:\n";
echo "admin.users.destroy with company_code=COMPANY_B, user=1:\n";
echo route('admin.users.destroy', ['company_code' => 'COMPANY_B', 'user' => 1]);
echo "\n\n";

echo "superadmin.users.destroy with user=1:\n";
echo route('superadmin.users.destroy', ['user' => 1]);
echo "\n";
