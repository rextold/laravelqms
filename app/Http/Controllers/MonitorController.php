<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoControl;
use App\Models\MarqueeSetting;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MonitorController extends Controller
{
    public function index(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();
        $companyCode = $request->route('organization_code');
        $onlineCounters = User::onlineCounters()->where('organization_id', $organization->id)->get();
        
        // Get active videos and format them properly for frontend
        $videos = Video::where('organization_id', $organization->id)
            ->active()
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'video_type' => $video->video_type,
                    'file_path' => $video->file_path,
                    'youtube_url' => $video->youtube_url,
                    'youtube_embed_url' => $video->youtube_embed_url,
                    'is_youtube' => $video->isYoutube(),
                    'is_file' => $video->isFile(),
                    'is_active' => $video->is_active,
                    'order' => $video->order,
                ];
            });
        
        $videoControl = VideoControl::getCurrent($organization->id);
        $marquee = MarqueeSetting::getActiveForOrganization($organization->id);
        $settings = OrganizationSetting::where('organization_id', $organization->id)->first();
        
        // Create default settings if none exist
        if (!$settings) {
            $settings = OrganizationSetting::create([
                'organization_id' => $organization->id,
                'code' => $organization->organization_code,
                'primary_color' => '#3b82f6',
                'secondary_color' => '#8b5cf6',
                'accent_color' => '#10b981',
                'text_color' => '#ffffff',
                'queue_number_digits' => 4,
                'is_active' => true,
            ]);
        }

        // Get current queue for each counter
        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $counterQueues[$counter->id] = $counter->getCurrentQueue();
        }

        // Pre-format counters in the same shape getData() returns so the blade can
        // seed STATE.initialCounters and render immediately — no first-poll wait.
        $initialCounterData = [];
        foreach ($onlineCounters as $counter) {
            $currentQueue = $counterQueues[$counter->id] ?? null;
            $initialCounterData[] = [
                'counter' => [
                    'id'                => $counter->id,
                    'counter_number'    => $counter->counter_number,
                    'display_name'      => $counter->display_name,
                    'short_description' => $counter->short_description ?? null,
                ],
                'queue' => $currentQueue ? [
                    'id'           => $currentQueue->id,
                    'queue_number' => (function () use ($currentQueue) {
                        $value = (string) ($currentQueue->queue_number ?? '');
                        $pos = strrpos($value, '-');
                        return $pos === false ? $value : substr($value, $pos + 1);
                    })(),
                    'status'       => $currentQueue->status,
                    'created_at'   => optional($currentQueue->created_at)->toDateTimeString(),
                    'called_at'    => optional($currentQueue->called_at)->toDateTimeString(),
                    'notified_at'  => optional($currentQueue->notified_at)->toDateTimeString(),
                ] : null,
                'recent_recall' => false,
            ];
        }

        // Pre-format waiting queues in the same shape getData() returns.
        $initialWaitingQueues = \App\Models\Queue::where('organization_id', $organization->id)
            ->where('status', 'waiting')
            ->select(['id', 'queue_number', 'counter_id', 'created_at'])
            ->with('counter:id,counter_number,display_name')
            ->orderBy('counter_id')
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->groupBy('counter_id')
            ->map(function ($queues) {
                $counter = optional($queues->first()->counter);
                return [
                    'counter_number' => $counter->counter_number ?? '?',
                    'display_name'   => $counter->display_name ?? 'Counter',
                    'queues'         => $queues->take(5)->map(function ($queue) {
                        $value = (string) ($queue->queue_number ?? '');
                        $pos = strrpos($value, '-');
                        return ['queue_number' => $pos === false ? $value : substr($value, $pos + 1)];
                    })->values()->all(),
                ];
            });

        // Include online counters that have no waiting queues (so panel shows them too)
        foreach ($onlineCounters as $counter) {
            if (!$initialWaitingQueues->has($counter->id)) {
                $initialWaitingQueues->put($counter->id, [
                    'counter_number' => $counter->counter_number,
                    'display_name'   => $counter->display_name ?? 'Counter',
                    'queues'         => [],
                ]);
            }
        }
        $initialWaitingQueues = $initialWaitingQueues->values()->all();

        // Check if refactored view exists, use it; otherwise fall back to original
        if (view()->exists('monitor.refactored')) {
            return view('monitor.refactored', compact(
                'onlineCounters', 'videos', 'videoControl', 'marquee',
                'counterQueues', 'settings', 'companyCode', 'organization',
                'initialCounterData', 'initialWaitingQueues'
            ));
        }

        return view('monitor.index', compact('onlineCounters', 'videos', 'videoControl', 'marquee', 'counterQueues', 'settings', 'companyCode', 'organization'));
    }

    public function getData(Request $request)
    {
        try {
            return $this->buildDataResponse($request);
        } catch (\Throwable $e) {
            // Never let getData() throw — the monitor JS would treat non-JSON
            // responses as a failure and enter the reconnect loop.
            \Illuminate\Support\Facades\Log::error('[MonitorController] getData error: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'org'   => $request->route('organization_code'),
            ]);

            return response()->json([
                'error'   => true,
                'message' => 'Temporary data unavailable. Retrying…',
                // Return empty-but-valid structure so the monitor JS keeps running.
                'counters'       => [],
                'video_control'  => null,
                'marquee'        => null,
                'waiting_queues' => [],
                'videos'         => [],
            ], 200)  // 200 so the JS poll loop continues normally
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
    }

    private function buildDataResponse(Request $request): \Illuminate\Http\JsonResponse
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();
        
        $onlineCounters = User::onlineCounters()
            ->where('organization_id', $organization->id)
            ->get(['id', 'organization_id', 'counter_number', 'display_name', 'short_description']);
        $videoControl = VideoControl::getCurrent($organization->id);
        $marquee = MarqueeSetting::getActiveForOrganization($organization->id);

        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $currentQueue = $counter->getCurrentQueue();
            $recentRecall = $currentQueue ? Cache::has('recall_queue_' . $currentQueue->id) : false;
            $counterQueues[] = [
                'counter' => $counter->only(['id', 'counter_number', 'display_name', 'short_description']),
                'queue' => $currentQueue ? [
                    'id' => $currentQueue->id,
                    // Display digits-only (strip any legacy prefix like 898979-1-0001)
                    'queue_number' => (function () use ($currentQueue) {
                        $value = (string) ($currentQueue->queue_number ?? '');
                        $pos = strrpos($value, '-');
                        return $pos === false ? $value : substr($value, $pos + 1);
                    })(),
                    'status' => $currentQueue->status,
                    'created_at' => optional($currentQueue->created_at)->toDateTimeString(),
                    'called_at' => optional($currentQueue->called_at)->toDateTimeString(),
                    'notified_at' => optional($currentQueue->notified_at)->toDateTimeString(),
                ] : null,
                'recent_recall' => $recentRecall,
            ];
        }

        // Get waiting queues grouped by counter for clearer display
        $waitingQueues = \App\Models\Queue::where('organization_id', $organization->id)
            ->where('status', 'waiting')
            ->select(['id', 'queue_number', 'counter_id', 'created_at'])
            ->with('counter:id,counter_number,display_name')
            ->orderBy('counter_id')
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->groupBy('counter_id')
            ->map(function ($queues) {
                $counter = optional($queues->first()->counter);
                return [
                    'counter_number' => $counter->counter_number ?? '?',
                    'display_name' => $counter->display_name ?? 'Counter',
                    'queues' => $queues->take(5)->map(function ($queue) {
                        return [
                            'queue_number' => (function () use ($queue) {
                                $value = (string) ($queue->queue_number ?? '');
                                $pos = strrpos($value, '-');
                                return $pos === false ? $value : substr($value, $pos + 1);
                            })(),
                        ];
                    })->values(),
                ];
            });

        // Add online counters that don't have waiting queues
        $onlineCountersWithoutQueues = $onlineCounters->filter(function ($counter) use ($waitingQueues) {
            return !$waitingQueues->has($counter->id);
        });

        foreach ($onlineCountersWithoutQueues as $counter) {
            $waitingQueues->put($counter->id, [
                'counter_number' => $counter->counter_number,
                'display_name' => $counter->display_name ?? 'Counter',
                'queues' => [], // Empty queues array for online counters without waiting queues
            ]);
        }

        $waitingQueues = $waitingQueues->values();

        // Include the live video list so the monitor JS can track add/remove without page reload
        $videos = \App\Models\Video::where('organization_id', $organization->id)
            ->active()
            ->get()
            ->map(function ($video) {
                return [
                    'id'                => $video->id,
                    'title'             => $video->title,
                    'video_type'        => $video->video_type,
                    'file_path'         => $video->file_path,
                    'youtube_url'       => $video->youtube_url,
                    'youtube_embed_url' => $video->youtube_embed_url,
                    'is_youtube'        => $video->isYoutube(),
                    'is_file'           => $video->isFile(),
                    'is_active'         => $video->is_active,
                    'order'             => $video->order,
                ];
            });

        return response()->json([
            'counters'       => $counterQueues,
            'video_control'  => $videoControl,
            'marquee'        => $marquee,
            'waiting_queues' => $waitingQueues,
            'videos'         => $videos,
        ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
    }
}