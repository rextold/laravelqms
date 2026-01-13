<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Organization;
use App\Models\User;
use App\Models\Queue;
use App\Services\QueueService;
use App\Events\CounterStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CounterApiController extends ApiController
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Get counter data and queue information
     */
    public function getCounterData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'counter_id' => 'required|exists:users,id',
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $counter = User::findOrFail($validated['counter_id']);

            // Verify counter belongs to organization
            if ((int) $counter->organization_id !== (int) $organization->id) {
                return $this->forbiddenResponse('Counter does not belong to this organization');
            }

            $buildPayload = function () use ($counter, $organization) {
                $currentQueue = $counter->getCurrentQueue();
                $waitingQueues = $counter->getWaitingQueues();
                $onlineCounters = User::onlineCounters()
                    ->where('organization_id', $organization->id)
                    ->where('id', '!=', $counter->id)
                    ->get(['id', 'display_name', 'counter_number']);

                return [
                    'counter' => [
                        'id' => $counter->id,
                        'name' => $counter->display_name,
                        'number' => $counter->counter_number,
                        'is_online' => $counter->is_online,
                    ],
                    'current_queue' => $currentQueue ? [
                        'id' => $currentQueue->id,
                        'queue_number' => $currentQueue->queue_number,
                        'status' => $currentQueue->status,
                        'created_at' => $currentQueue->created_at->toISOString(),
                        'called_at' => $currentQueue->called_at ? $currentQueue->called_at->toISOString() : null,
                    ] : null,
                    'waiting_queues' => $waitingQueues->map(function ($queue) {
                        return [
                            'id' => $queue->id,
                            'queue_number' => $queue->queue_number,
                            'created_at' => $queue->created_at->toISOString(),
                        ];
                    }),
                    'online_counters' => $onlineCounters->map(function ($counter) {
                        return [
                            'id' => $counter->id,
                            'name' => $counter->display_name,
                            'number' => $counter->counter_number,
                        ];
                    }),
                    'waiting_count' => $waitingQueues->count(),
                ];
            };

            // Use cache if available
            $cacheStore = (string) config('cache.default');
            if ($cacheStore !== 'database') {
                $cacheKey = "counter.data.{$counter->id}";
                $payload = Cache::remember($cacheKey, 1, $buildPayload);
            } else {
                $payload = $buildPayload();
            }

            return $this->successResponse($payload);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get counter data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle counter online/offline status
     */
    public function toggleOnlineStatus(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'counter_id' => 'required|exists:users,id',
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $counter = User::findOrFail($validated['counter_id']);

            // Verify counter belongs to organization
            if ((int) $counter->organization_id !== (int) $organization->id) {
                return $this->forbiddenResponse('Counter does not belong to this organization');
            }

            $counter->is_online = !$counter->is_online;
            $counter->save();

            // Broadcast status update
            event(new CounterStatusUpdated($organization->organization_code, $counter->id, $counter->is_online ? 'online' : 'offline'));

            return $this->successResponse([
                'counter' => [
                    'id' => $counter->id,
                    'is_online' => $counter->is_online,
                    'status' => $counter->is_online ? 'online' : 'offline'
                ]
            ], $counter->is_online ? 'Counter is now online' : 'Counter is now offline');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to toggle counter status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Call next queue
     */
    public function callNext(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'counter_id' => 'required|exists:users,id',
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $counter = User::findOrFail($validated['counter_id']);

            // Verify counter belongs to organization
            if ((int) $counter->organization_id !== (int) $organization->id) {
                return $this->forbiddenResponse('Counter does not belong to this organization');
            }

            $queue = $this->queueService->callNextQueue($counter);

            // Invalidate cache after state change
            $this->invalidateCounterCache($counter);

            if (!$queue) {
                return $this->errorResponse('No queues waiting', 404);
            }

            return $this->successResponse([
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'status' => $queue->status,
                    'called_at' => $queue->called_at->toISOString(),
                ]
            ], 'Queue called successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to call next queue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Move to next queue (complete current and call next)
     */
    public function moveToNext(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'counter_id' => 'required|exists:users,id',
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $counter = User::findOrFail($validated['counter_id']);

            // Verify counter belongs to organization
            if ((int) $counter->organization_id !== (int) $organization->id) {
                return $this->forbiddenResponse('Counter does not belong to this organization');
            }

            $result = $this->queueService->moveToNextQueue($counter);

            // Invalidate cache after state change
            $this->invalidateCounterCache($counter);

            return $this->successResponse([
                'completed_queue' => $result['completed_queue'] ? [
                    'id' => $result['completed_queue']->id,
                    'queue_number' => $result['completed_queue']->queue_number,
                    'status' => $result['completed_queue']->status,
                ] : null,
                'next_queue' => $result['next_queue'] ? [
                    'id' => $result['next_queue']->id,
                    'queue_number' => $result['next_queue']->queue_number,
                    'status' => $result['next_queue']->status,
                    'called_at' => $result['next_queue']->called_at->toISOString(),
                ] : null,
            ], 'Moved to next queue successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to move to next queue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Transfer queue to another counter
     */
    public function transferQueue(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'queue_id' => 'required|exists:queues,id',
                'to_counter_id' => 'required|exists:users,id',
                'from_counter_id' => 'required|exists:users,id',
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $fromCounter = User::findOrFail($validated['from_counter_id']);
            $toCounter = User::findOrFail($validated['to_counter_id']);
            $queue = Queue::findOrFail($validated['queue_id']);

            // Verify counters belong to organization
            if ((int) $fromCounter->organization_id !== (int) $organization->id || 
                (int) $toCounter->organization_id !== (int) $organization->id) {
                return $this->forbiddenResponse('Counters do not belong to this organization');
            }

            // Verify this queue belongs to the from counter
            if ($queue->counter_id !== $fromCounter->id) {
                return $this->errorResponse('This queue does not belong to the specified counter', 422);
            }

            // Only transfer to other counters
            if (!$toCounter->isCounter()) {
                return $this->errorResponse('Target user is not a counter', 422);
            }

            // Verify target counter is online
            if (!$toCounter->is_online) {
                return $this->errorResponse('Target counter is offline', 422);
            }

            // Verify target counter is different from current counter
            if ($toCounter->id === $fromCounter->id) {
                return $this->errorResponse('Cannot transfer to the same counter', 422);
            }

            $newQueue = $this->queueService->transferQueue($queue, $toCounter);

            // Invalidate cache for both counters
            $this->invalidateCounterCache($fromCounter);
            $this->invalidateCounterCache($toCounter);

            return $this->successResponse([
                'queue' => [
                    'id' => $newQueue->id,
                    'queue_number' => $newQueue->queue_number,
                    'status' => $newQueue->status,
                    'counter_id' => $newQueue->counter_id,
                    'counter_name' => $toCounter->display_name,
                ]
            ], 'Queue transferred successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to transfer queue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get online counters for an organization
     */
    public function getOnlineCounters(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $counters = User::onlineCounters()
                ->where('organization_id', $organization->id)
                ->get(['id', 'display_name', 'counter_number', 'short_description']);

            return $this->successResponse([
                'counters' => $counters->map(function ($counter) {
                    return [
                        'id' => $counter->id,
                        'name' => $counter->display_name,
                        'number' => $counter->counter_number,
                        'description' => $counter->short_description,
                    ];
                }),
                'count' => $counters->count(),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get online counters: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Invalidate counter cache
     */
    private function invalidateCounterCache(User $counter): void
    {
        $cacheStore = (string) config('cache.default');
        if ($cacheStore !== 'database') {
            $cacheKey = "counter.data.{$counter->id}";
            Cache::forget($cacheKey);
        }
    }
}