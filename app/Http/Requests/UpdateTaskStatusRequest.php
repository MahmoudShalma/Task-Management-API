<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');
        return $this->user()->id === $task->assigned_to;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,completed,canceled',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Status must be one of: pending, completed, canceled',
        ];
    }
}
