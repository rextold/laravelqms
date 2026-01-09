<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoControl;
use App\Models\MarqueeSetting;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MonitorController extends Controller
{
    public function index(Request $request)
    {
        $companyCode = $request->route('company_code');
        $onlineCounters = User::onlineCounters()->get();
        $videos = Video::active()->get();
        $videoControl = VideoControl::getCurrent();
        $marquee = MarqueeSetting::getActive();
        $settings = CompanySetting::getSettings();

        // Get current queue for each counter
        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $counterQueues[$counter->id] = $counter->getCurrentQueue();
        }

        return view('monitor.index', compact('onlineCounters', 'videos', 'videoControl', 'marquee', 'counterQueues', 'settings', 'companyCode'));
    }

    public function getData()
    {
        $onlineCounters = User::onlineCounters()->get();
        $videoControl = VideoControl::getCurrent();
        $marquee = MarqueeSetting::getActive();

        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $currentQueue = $counter->getCurrentQueue();
            $recentRecall = $currentQueue ? Cache::has('recall_queue_' . $currentQueue->id) : false;
            $counterQueues[] = [
                'counter' => $counter,
                'queue' => $currentQueue,
                'recent_recall' => $recentRecall,
            ];
        }

        // Get waiting queues grouped by counter for clearer display
        $waitingQueues = \App\Models\Queue::where('status', 'waiting')
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
