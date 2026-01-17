<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CounterRequest extends FormRequest
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
        $action = $this->route()->getActionMethod();

        return match ($action) {
            'transferQueue' => [
                'queue_id' => 'required|integer|exists:queues,id',
                'to_counter_id' => 'required|integer|exists:users,id',
            ],
            'recallQueue' => [
                'queue_id' => 'sometimes|integer|exists:queues,id',
            ],
            default => [],
        };
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
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('to_counter_id')) {
                $targetCounter = \App\Models\User::find($this->to_counter_id);
                if ($targetCounter && !$targetCounter->is_online) {
                    $validator->errors()->add('to_counter_id', 'Target counter must be online.');
                }
            }
        });
    }
}