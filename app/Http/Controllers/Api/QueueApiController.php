<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Organization;
use App\Models\User;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QueueApiController extends ApiController
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Generate a new queue number
     */
    public function generateQueue(Request $request): JsonResponse
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
                return $this->errorResponse('Invalid counter for this organization', 422);
            }

            // Verify counter is online
            if (!$counter->is_online) {
                return $this->errorResponse('Counter is currently offline', 422);
            }

            $queue = $this->queueService->createQueue($counter);

            return $this->successResponse([
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'counter_id' => $queue->counter_id,
                    'counter_name' => $counter->display_name,
                    'counter_number' => $counter->counter_number,
                    'status' => $queue->status,
                    'created_at' => $queue->created_at->toISOString(),
                ]
            ], 'Queue generated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate queue: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get queue status
     */
    public function getQueueStatus(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'queue_id' => 'required|integer',
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $queue = Queue::where('id', $validated['queue_id'])
                ->where('organization_id', $organization->id)
                ->with('counter:id,counter_number,display_name')
                ->first();

            if (!$queue) {
                return $this->notFoundResponse('Queue not found');
            }

            return $this->successResponse([
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'status' => $queue->status,
                    'counter' => $queue->counter ? [
                        'id' => $queue->counter->id,
                        'name' => $queue->counter->display_name,
                        'number' => $queue->counter->counter_number,
                    ] : null,
                    'created_at' => $queue->created_at->toISOString(),
                    'called_at' => $queue->called_at ? $queue->called_at->toISOString() : null,
                    'completed_at' => $queue->completed_at ? $queue->completed_at->toISOString() : null,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get queue status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get waiting queues for an organization
     */
    public function getWaitingQueues(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_code' => 'required|string'
            ]);

            $organization = Organization::where('organization_code', $validated['organization_code'])->first();
            if (!$organization) {
                return $this->notFoundResponse('Organization not found');
            }

            $waitingQueues = Queue::where('organization_id', $organization->id)
                ->where('status', 'waiting')
                ->with('counter:id,counter_number,display_name')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'queue_number' => $queue->queue_number,
                        'counter' => $queue->counter ? [
                            'id' => $queue->counter->id,
                            'name' => $queue->counter->display_name,
                            'number' => $queue->counter->counter_number,
                        ] : null,
                        'created_at' => $queue->created_at->toISOString(),
                    ];
                });

            return $this->successResponse([
                'queues' => $waitingQueues,
                'count' => $waitingQueues->count()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get waiting queues: ' . $e->getMessage(), 500);
        }
    }
}