<?php

namespace App\Repositories;

use App\Filters\TaskFilter;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Task::with(['assignedUser', 'creator', 'dependencies']);

        $filter = new TaskFilter($query, $filters);
        $filter->apply();

        return $query->latest()->get();
    }

    public function find(int $id): ?Task
    {
        return Task::with(['assignedUser', 'creator', 'dependencies', 'dependents'])->find($id);
    }

    public function create(array $data): Task
    {
        $task = Task::create($data);
        return $task->load(['assignedUser', 'creator', 'dependencies']);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->load(['assignedUser', 'creator', 'dependencies']);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function addDependencies(Task $task, array $dependencyIds): Task
    {
        $task->dependencies()->syncWithoutDetaching($dependencyIds);
        return $task->load(['dependencies']);
    }

    public function removeDependencies(Task $task, array $dependencyIds): Task
    {
        $task->dependencies()->detach($dependencyIds);
        return $task->load(['dependencies']);
    }

    public function canBeCompleted(Task $task): bool
    {
        return $task->canBeCompleted();
    }

    public function canBeDeleted(Task $task): bool
    {
        return $task->canBeDeleted();
    }
}
