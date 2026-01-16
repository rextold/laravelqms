<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Check ===" . PHP_EOL;

try {
    echo "Organizations:" . PHP_EOL;
    $organizations = App\Models\Organization::all();
    foreach($organizations as $org) {
        echo "- ID: {$org->id}, Code: {$org->organization_code}, Name: {$org->organization_name}" . PHP_EOL;
    }
    
    echo PHP_EOL . "Counter Users:" . PHP_EOL;
    $counters = App\Models\User::where('role', 'counter')->get();
    foreach($counters as $user) {
        echo "- Username: {$user->username}, Role: {$user->role}, Org ID: {$user->organization_id}, Online: " . ($user->is_online ? 'Yes' : 'No') . PHP_EOL;
    }
    
    echo PHP_EOL . "All Users:" . PHP_EOL;
    $users = App\Models\User::all();
    foreach($users as $user) {
        echo "- Username: {$user->username}, Role: {$user->role}, Org ID: {$user->organization_id}" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}