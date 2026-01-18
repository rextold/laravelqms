<?php
// Usage: php scripts/add_and_play_youtube.php "https://www.youtube.com/watch?v=VIDEOID" "Optional Title"
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 2) {
    echo "Usage: php scripts/add_and_play_youtube.php \"YOUTUBE_URL\" \"Optional Title\"\n";
    exit(1);
}

$url = $argv[1];
$title = $argv[2] ?? 'YouTube Video';

$org = \App\Models\Organization::first();
if (!$org) {
    echo "No organization found.\n";
    exit(1);
}

$video = \App\Models\Video::create([
    'title' => $title,
    'video_type' => 'youtube',
    'youtube_url' => $url,
    'order' => (\App\Models\Video::where('organization_id', $org->id)->max('order') ?? 0) + 1,
    'is_active' => true,
    'organization_id' => $org->id,
]);

$control = \App\Models\VideoControl::getCurrent();
$control->update(['current_video_id' => $video->id, 'is_playing' => true]);

try {
    event(new \App\Events\VideoControlUpdated($control, []));
    echo "CREATED_AND_PLAYED: {$video->id}\n";
} catch (\Throwable $e) {
    echo "CREATED_BUT_BROADCAST_FAILED: {$video->id} (" . $e->getMessage() . ")\n";
}
