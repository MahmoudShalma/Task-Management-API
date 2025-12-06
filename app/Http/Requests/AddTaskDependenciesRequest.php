<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTaskDependenciesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'dependencies' => 'required|array',
            'dependencies.*' => 'exists:tasks,id',
        ];
    }

    public function messages(): array
    {
        return [
            'dependencies.required' => 'Dependencies are required',
            'dependencies.array' => 'Dependencies must be an array',
            'dependencies.*.exists' => 'One or more dependency tasks do not exist',
        ];
    }
}
