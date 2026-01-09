<?php
// Quick test to verify route generation
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Create a request for the COMPANY_B/admin/users context
$request = \Illuminate\Http\Request::create('http://127.0.0.1:8000/COMPANY_B/admin/users', 'GET');
$kernel->handle($request);

// Now the container should be set up, test route generation
echo "Testing route generation:\n\n";

// Test 1: Admin route with company_code and user object
echo "Admin route (using object):\n";
echo route('admin.users.destroy', ['company_code' => 'COMPANY_B', 'user' => new \App\Models\User(['id' => 2])]);
echo "\n\n";

// Test 2: SuperAdmin route with user object
echo "SuperAdmin route (using object):\n";
echo route('superadmin.users.destroy', new \App\Models\User(['id' => 2]));
echo "\n\n";

// Test 3: With array parameter
echo "Admin route (using array with ID):\n";
echo route('admin.users.destroy', ['company_code' => 'COMPANY_B', 'user' => 2]);
echo "\n";
