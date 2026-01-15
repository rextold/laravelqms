<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\CounterController;
use App\Models\User;
use App\Models\Queue;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Recall Functionality\n";
echo "============================\n\n";

// Find a counter user
$counter = User::where('role', 'counter')->first();
if (!$counter) {
    echo "❌ No counter users found. Please run php artisan db:seed first.\n";
    exit(1);
}

echo "✅ Found counter: {$counter->username} (ID: {$counter->id})\n";

// Check if there are any queues
$queueCount = Queue::count();
echo "📊 Total queues in database: {$queueCount}\n";

if ($queueCount === 0) {
    echo "ℹ️  No queues found. Creating a test queue...\n";
    
    // Create a test queue
    $queue = Queue::create([
        'queue_number' => '20260115-01-0001',
        'counter_id' => $counter->id,
        'status' => 'skipped',
        'skipped_at' => now(),
        'called_at' => now()->subMinutes(5),
        'organization_id' => $counter->organization_id
    ]);
    
    echo "✅ Created test queue: {$queue->queue_number} (ID: {$queue->id})\n";
} else {
    // Find a skipped queue for this counter
    $queue = Queue::where('counter_id', $counter->id)
                  ->where('status', 'skipped')
                  ->first();
    
    if (!$queue) {
        // Create a skipped queue for testing
        $queue = Queue::create([
            'queue_number' => '20260115-01-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'counter_id' => $counter->id,
            'status' => 'skipped',
            'skipped_at' => now(),
            'called_at' => now()->subMinutes(5),
            'organization_id' => $counter->organization_id
        ]);
        
        echo "✅ Created test skipped queue: {$queue->queue_number} (ID: {$queue->id})\n";
    } else {
        echo "✅ Found existing skipped queue: {$queue->queue_number} (ID: {$queue->id})\n";
    }
}

echo "\n🧪 Testing Recall Functionality\n";
echo "--------------------------------\n";

// Simulate authentication
auth()->login($counter);
echo "✅ Authenticated as counter: {$counter->username}\n";

// Create a mock request
$request = Request::create('/counter/recall', 'GET', ['queue_id' => $queue->id]);

// Test the recall functionality
$controller = new CounterController(app(\App\Services\QueueService::class));

echo "🔄 Attempting to recall queue {$queue->queue_number}...\n";

try {
    $response = $controller->recallQueue($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "📤 Response Status: " . $response->getStatusCode() . "\n";
    echo "📄 Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if ($responseData['success'] ?? false) {
        echo "✅ Recall successful!\n";
        
        // Verify the queue status changed
        $updatedQueue = Queue::find($queue->id);
        echo "📊 Updated queue status: {$updatedQueue->status}\n";
        echo "📊 Updated queue called_at: {$updatedQueue->called_at}\n";
        echo "📊 Updated queue skipped_at: " . ($updatedQueue->skipped_at ?? 'null') . "\n";
    } else {
        echo "❌ Recall failed: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n📋 Test completed.\n";