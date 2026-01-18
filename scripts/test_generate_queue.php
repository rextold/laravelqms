<?php
// One-off test to exercise QueueService::createQueue for first online counter
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Organization;
use App\Models\User;
use App\Services\QueueService;

// Force broadcasting off and use sync queue for this local test to avoid enqueueing jobs
config(['broadcasting.default' => 'null']);
config(['queue.default' => 'sync']);

$org = Organization::first();
if (!$org) {
    echo "NO_ORG\n";
    exit(1);
}

// Find an online counter in this organization
$counter = User::where('organization_id', $org->id)->where('is_online', true)->first();
if (!$counter) {
    echo "NO_ONLINE_COUNTER_FOUND for org {$org->id}\n";
    exit(2);
}

$service = app(QueueService::class);
try {
    $queue = $service->createQueue($counter);
    $payload = sprintf('%s|%s|%s', $queue->queue_number, $queue->id, $queue->created_at->timestamp ?? time());
    $key = config('app.key') ?? env('APP_KEY');
    $signature = hash_hmac('sha256', $payload, $key);

    echo json_encode([
        'success' => true,
        'queue_number' => $queue->queue_number,
        'queue_id' => $queue->id,
        'signature' => $signature,
        'counter_id' => $counter->id,
        'organization_id' => $org->id,
    ], JSON_PRETTY_PRINT) . "\n";
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]) . "\n";
    exit(3);
}
