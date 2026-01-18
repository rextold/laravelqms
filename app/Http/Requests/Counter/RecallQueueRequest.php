<?php

namespace App\Http\Requests\Counter;

use Illuminate\Foundation\Http\FormRequest;

class RecallQueueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCounter();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'queue_id' => ['required', 'integer', 'exists:queues,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'queue_id.required' => 'Queue ID is required.',
            'queue_id.exists' => 'The selected queue does not exist.',
        ];
    }

    /**
     * Get the queue ID from query or input
     */
    public function getQueueId(): int
    {
        return $this->input('queue_id') ?? $this->query('queue_id');
    }
}