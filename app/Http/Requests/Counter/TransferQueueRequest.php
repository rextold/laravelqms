<?php

namespace App\Http\Requests\Counter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferQueueRequest extends FormRequest
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
            'to_counter_id' => ['required', 'integer', 'exists:users,id', Rule::exists('users', 'id')->where('role', 'counter')],
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
            'to_counter_id.required' => 'Target counter ID is required.',
            'to_counter_id.exists' => 'The selected counter does not exist.',
        ];
    }

    /**
     * Get the queue ID from query or input
     */
    public function getQueueId(): int
    {
        return $this->input('queue_id') ?? $this->query('queue_id');
    }

    /**
     * Get the target counter ID from query or input
     */
    public function getToCounterId(): int
    {
        return $this->input('to_counter_id') ?? $this->query('to_counter_id');
    }
}