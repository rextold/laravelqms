<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoControl;
use App\Models\MarqueeSetting;

class MonitorController extends Controller
{
    public function index()
    {
        $onlineCounters = User::onlineCounters()->get();
        $videos = Video::active()->get();
        $videoControl = VideoControl::getCurrent();
        $marquee = MarqueeSetting::getActive();

        // Get current queue for each counter
        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $counterQueues[$counter->id] = $counter->getCurrentQueue();
        }

        return view('monitor.index', compact('onlineCounters', 'videos', 'videoControl', 'marquee', 'counterQueues'));
    }

    public function getData()
    {
        $onlineCounters = User::onlineCounters()->get();
        $videoControl = VideoControl::getCurrent();
        $marquee = MarqueeSetting::getActive();

        $counterQueues = [];
        foreach ($onlineCounters as $counter) {
            $currentQueue = $counter->getCurrentQueue();
            $counterQueues[] = [
                'counter' => $counter,
                'queue' => $currentQueue
            ];
        }

        return response()->json([
            'counters' => $counterQueues,
            'video_control' => $videoControl,
            'marquee' => $marquee,
        ]);
    }
}
