<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\CounterController;
use App\Models\User;
use App\Models\Queue;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Counter Operations with GET Method\n";
echo "==========================================\n\n";

// Find a counter user
$counter = User::where('role', 'counter')->first();
if (!$counter) {
    echo "❌ No counter users found. Please run php artisan db:seed first.\n";
    exit(1);
}

echo "✅ Found counter: {$counter->username} (ID: {$counter->id})\n";

// Authenticate as the counter
auth()->login($counter);
echo "✅ Authenticated as counter: {$counter->username}\n\n";

// Initialize controller
$controller = new CounterController(app(\App\Services\QueueService::class));

// Test operations
$operations = [
    'toggle-online' => 'Toggle Online Status',
    'call-next' => 'Call Next Queue',
    'notify' => 'Notify Customer',
    'skip' => 'Skip Queue',
    'move-next' => 'Move to Next'
];

foreach ($operations as $operation => $description) {
    echo "🧪 Testing: {$description}\n";
    echo str_repeat('-', 40) . "\n";
    
    try {
        // Create a mock GET request
        $request = Request::create("/counter/{$operation}", 'GET');
        
        // Call the appropriate method
        switch ($operation) {
            case 'toggle-online':
                $response = $controller->toggleOnline($request);
                break;
            case 'call-next':
                $response = $controller->callNext();
                break;
            case 'notify':
                $response = $controller->notifyCustomer($request);
                break;
            case 'skip':
                $response = $controller->skipQueue($request);
                break;
            case 'move-next':
                $response = $controller->moveToNext();
                break;
            default:
                echo "❌ Unknown operation: {$operation}\n";
                continue 2;
        }
        
        $responseData = json_decode($response->getContent(), true);
        $statusCode = $response->getStatusCode();
        
        echo "📤 Response Status: {$statusCode}\n";
        
        if ($statusCode === 200) {
            echo "✅ Operation successful\n";
            if (isset($responseData['success']) && $responseData['success']) {
                echo "✅ Success flag: true\n";
            }
            if (isset($responseData['message'])) {
                echo "💬 Message: {$responseData['message']}\n";
            }
        } else {
            echo "⚠️  Non-200 status code\n";
            if (isset($responseData['message'])) {
                echo "💬 Error: {$responseData['message']}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
        echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    echo "\n";
}

// Test data retrieval
echo "🧪 Testing: Data Retrieval\n";
echo str_repeat('-', 40) . "\n";

try {
    $response = $controller->getData();
    $responseData = json_decode($response->getContent(), true);
    $statusCode = $response->getStatusCode();
    
    echo "📤 Response Status: {$statusCode}\n";
    
    if ($statusCode === 200 && isset($responseData['success']) && $responseData['success']) {
        echo "✅ Data retrieval successful\n";
        echo "📊 Online status: " . ($responseData['is_online'] ? 'true' : 'false') . "\n";
        echo "📊 Waiting queues: " . count($responseData['waiting_queues']) . "\n";
        echo "📊 Skipped queues: " . count($responseData['skipped']) . "\n";
        echo "📊 Online counters: " . count($responseData['online_counters']) . "\n";
    } else {
        echo "❌ Data retrieval failed\n";
        if (isset($responseData['message'])) {
            echo "💬 Error: {$responseData['message']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n📋 All tests completed.\n";