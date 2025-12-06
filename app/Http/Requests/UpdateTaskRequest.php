<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,completed,canceled',
            'due_date' => 'nullable|date|after_or_equal:today',
            'assigned_to' => 'nullable|exists:users,id',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: pending, completed, canceled',
            'due_date.after_or_equal' => 'Due date must be today or a future date',
            'assigned_to.exists' => 'The selected user does not exist',
            'dependencies.*.exists' => 'One or more dependency tasks do not exist',
        ];
    }
}
