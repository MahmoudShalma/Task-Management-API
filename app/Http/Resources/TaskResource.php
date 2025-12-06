<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'assigned_to' => [
                'id' => $this->assignedUser?->id,
                'name' => $this->assignedUser?->name,
                'email' => $this->assignedUser?->email,
            ],
            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ],
            'dependencies' => $this->whenLoaded('dependencies', function () {
                return $this->dependencies->map(function ($dependency) {
                    return [
                        'id' => $dependency->id,
                        'title' => $dependency->title,
                        'status' => $dependency->status,
                    ];
                });
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
