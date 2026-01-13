<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Organization;
use App\Models\User;
use App\Models\Queue;
use App\Models\Video;
use App\Models\VideoControl;
use App\Models\MarqueeSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MonitorApiController extends ApiController
{
    /**
     * Get monitor display data
     */
    public function getMonitorData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            // Get online counters with their current and waiting queues
            $onlineCounters = User::onlineCounters()
                ->where('organization_id', $organization->id)
                ->with(['currentQueue', 'waitingQueues' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->orderBy('counter_number')
                ->get();

            // Get video control settings
            $videoControl = VideoControl::getCurrent();

            // Get active marquee
            $marquee = MarqueeSetting::where('organization_id', $organization->id)
                ->where('is_active', true)
                ->first();

            // Get all waiting queues for the organization
            $waitingQueues = Queue::where('organization_id', $organization->id)
                ->where('status', 'waiting')
                ->with('counter:id,counter_number,display_name')
                ->orderBy('created_at', 'asc')
                ->get();

            // Format counter data
            $counterQueues = $onlineCounters->map(function ($counter) {
                return [
                    'counter' => [
                        'id' => $counter->id,
                        'name' => $counter->display_name,
                        'number' => $counter->counter_number,
                    ],
                    'current_queue' => $counter->currentQueue ? [
                        'id' => $counter->currentQueue->id,
                        'queue_number' => $counter->currentQueue->queue_number,
                        'status' => $counter->currentQueue->status,
                        'called_at' => $counter->currentQueue->called_at ? $counter->currentQueue->called_at->toISOString() : null,
                        'display_number' => (function() use ($counter) {
                            $value = $counter->currentQueue->queue_number;
                            $pos = strrpos($value, '-');
                            return $pos === false ? $value : substr($value, $pos + 1);
                        })(),
                    ] : null,
                    'waiting_queues' => $counter->waitingQueues->map(function ($queue) {
                        return [
                            'id' => $queue->id,
                            'queue_number' => $queue->queue_number,
                            'created_at' => $queue->created_at->toISOString(),
                            'display_number' => (function() use ($queue) {
                                $value = $queue->queue_number;
                                $pos = strrpos($value, '-');
                                return $pos === false ? $value : substr($value, $pos + 1);
                            })(),
                        ];
                    }),
                    'waiting_count' => $counter->waitingQueues->count(),
                ];
            });

            return $this->successResponse([
                'counters' => $counterQueues,
                'video_control' => [
                    'is_playing' => $videoControl->is_playing,
                    'volume' => $videoControl->volume,
                    'bell_volume' => $videoControl->bell_volume,
                    'current_video_id' => $videoControl->current_video_id,
                ],
                'marquee' => $marquee ? [
                    'id' => $marquee->id,
                    'text' => $marquee->text,
                    'speed' => $marquee->speed,
                    'color' => $marquee->color,
                    'background_color' => $marquee->background_color,
                ] : null,
                'waiting_queues' => $waitingQueues->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'queue_number' => $queue->queue_number,
                        'counter' => $queue->counter ? [
                            'id' => $queue->counter->id,
                            'name' => $queue->counter->display_name,
                            'number' => $queue->counter->counter_number,
                        ] : null,
                        'created_at' => $queue->created_at->toISOString(),
                        'display_number' => (function() use ($queue) {
                            $value = $queue->queue_number;
                            $pos = strrpos($value, '-');
                            return $pos === false ? $value : substr($value, $pos + 1);
                        })(),
                    ];
                }),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get monitor data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get video playlist for monitor
     */
    public function getVideoPlaylist(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $videos = Video::where('organization_id', $organization->id)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();

            $videoControl = VideoControl::getCurrent();

            return $this->successResponse([
                'videos' => $videos->map(function ($video) {
                    return [
                        'id' => $video->id,
                        'title' => $video->title,
                        'video_type' => $video->video_type,
                        'youtube_url' => $video->youtube_url,
                        'youtube_embed_url' => $video->youtube_embed_url,
                        'file_path' => $video->file_path ? asset('storage/' . $video->file_path) : null,
                        'is_youtube' => $video->isYoutube(),
                        'is_file' => $video->isFile(),
                        'order' => $video->order,
                    ];
                }),
                'control' => [
                    'is_playing' => $videoControl->is_playing,
                    'volume' => $videoControl->volume,
                    'bell_volume' => $videoControl->bell_volume,
                    'current_video_id' => $videoControl->current_video_id,
                ],
                'count' => $videos->count(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get video playlist: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get organization settings for monitor
     */
    public function getOrganizationSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $settings = $organization->settings;

            if (!$settings) {
                return $this->notFoundResponse('Organization settings not found');
            }

            return $this->successResponse([
                'organization' => [
                    'name' => $organization->organization_name,
                    'code' => $organization->organization_code,
                ],
                'settings' => [
                    'primary_color' => $settings->primary_color,
                    'secondary_color' => $settings->secondary_color,
                    'accent_color' => $settings->accent_color,
                    'text_color' => $settings->text_color,
                    'organization_logo' => $settings->organization_logo ? asset('storage/' . $settings->organization_logo) : null,
                    'organization_phone' => $settings->organization_phone,
                    'organization_email' => $settings->organization_email,
                    'organization_address' => $settings->organization_address,
                    'queue_number_digits' => $settings->queue_number_digits,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get organization settings: ' . $e->getMessage(), 500);
        }
    }
}