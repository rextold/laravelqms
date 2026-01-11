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
        $videos = Video::where('organization_id', $organization->id)->active()->get();
        $videoControl = VideoControl::getCurrent();
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

        return view('monitor.index', compact('onlineCounters', 'videos', 'videoControl', 'marquee', 'counterQueues', 'settings', 'companyCode', 'organization'));
    }

    public function getData(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();
        
        $onlineCounters = User::onlineCounters()->where('organization_id', $organization->id)->get();
        $videoControl = VideoControl::getCurrent();
        $marquee = MarqueeSetting::getActiveForOrganization($organization->id);

        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $currentQueue = $counter->getCurrentQueue();
            $recentRecall = $currentQueue ? Cache::has('recall_queue_' . $currentQueue->id) : false;
            $counterQueues[] = [
                'counter' => $counter->only(['id', 'counter_number', 'display_name', 'short_description']),
                'queue' => $currentQueue ? [
                    'id' => $currentQueue->id,
                    'queue_number' => $currentQueue->queue_number,
                    'status' => $currentQueue->status,
                    'created_at' => $currentQueue->created_at,
                ] : null,
                'recent_recall' => $recentRecall,
            ];
        }

        // Get waiting queues grouped by counter for clearer display
        $waitingQueues = \App\Models\Queue::where('organization_id', $organization->id)
            ->where('status', 'waiting')
            ->with('counter:id,counter_number,display_name')
            ->orderBy('counter_id')
            ->orderBy('updated_at')
            ->get()
            ->groupBy('counter_id')
            ->map(function ($queues) {
                $counter = optional($queues->first()->counter);
                return [
                    'counter_number' => $counter->counter_number ?? '?',
                    'display_name' => $counter->display_name ?? 'Counter',
                    'queues' => $queues->take(5)->map(function ($queue) {
                        return [
                            'queue_number' => $queue->queue_number,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'counters' => $counterQueues,
            'video_control' => $videoControl,
            'marquee' => $marquee,
            'waiting_queues' => $waitingQueues,
        ]);
    }
}
