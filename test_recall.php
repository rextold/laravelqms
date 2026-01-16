<?php

// test_recall.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "DEBUG: Script execution started.\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Http/Controllers/CounterController.php';

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CounterController;

echo "DEBUG: Bootstrapping Laravel...\n";

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "DEBUG: Laravel bootstrapped.\n";

echo "Testing Recall Functionality\n";
echo "============================\n\n";

// Find a counter user to authenticate
echo "DEBUG: Finding a counter user...\n";
$counterUser = User::where('role', 'counter')->first();
if (!$counterUser) {
    echo "❌ No counter user found in the database. Please seed the database with a counter user.\n";
    exit(1);
}
echo "DEBUG: Found counter user.\n";
echo "✅ Found counter: {$counterUser->name} (ID: {$counterUser->id})\n";

// Manually authenticate the counter user
Auth::login($counterUser);

// Check total queues
$totalQueues = Queue::count();
echo "📊 Total queues in database: {$totalQueues}\n";

// Find a skipped queue to recall
$queue = Queue::where('status', 'skipped')->first();

if (!$queue) {
    echo "❌ No skipped queues found to test the recall functionality.\n";
    // Optional: Create a dummy skipped queue for testing
    $queue = Queue::factory()->create(['status' => 'skipped']);
    echo "ℹ️ Created a dummy skipped queue for testing: {$queue->queue_number}\n";
}

if ($queue) {
    echo "✅ Found existing skipped queue: {$queue->queue_number} (ID: {$queue->id})\n\n";

    echo "🧪 Testing Recall Functionality\n";
    echo "--------------------------------\n";

    if (Auth::check()) {
        echo "✅ Authenticated as counter: " . Auth::user()->name . "\n\n";
    } else {
        echo "❌ Failed to authenticate as counter.\n\n";
        exit(1);
    }

    // Create a new request instance for the recall
    $request = Request::create('/counter/recall', 'GET', ['queue_id' => $queue->id]);

    // Test the recall functionality
    $queueService = app(\App\Services\QueueService::class);
    $controller = new CounterController($queueService);

    echo "🔄 Attempting to recall queue {$queue->queue_number}...\n";

    try {
        $response = $controller->recallQueue($request);
        $recalledQueue = Queue::find($queue->id);

        if ($recalledQueue->status === 'serving') {
            echo "✅ Queue recalled successfully! Status is now 'serving'.\n";
            echo "   - Queue Number: {$recalledQueue->queue_number}\n";
            echo "   - Counter: {$recalledQueue->counter->name}\n";
            echo "   - Called At: {$recalledQueue->called_at}\n";
        } else {
            echo "❌ Recall failed. Queue status is still '{$recalledQueue->status}'.\n";
            echo "   - Response: " . $response->getContent() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ An error occurred during recall: " . $e->getMessage() . "\n";
        echo "   - File: " . $e->getFile() . "\n";
        echo "   - Line: " . $e->getLine() . "\n";
    }
} else {
    echo "🤷 No skipped queues available to test recall.\n";
}

echo "\n";