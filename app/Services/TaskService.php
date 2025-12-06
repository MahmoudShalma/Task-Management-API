<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    protected TaskRepositoryInterface $repository;

    public function __construct(TaskRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTasks(User $user, array $filters = []): Collection
    {
        if ($user->isUser()) {
            $filters['user_id'] = $user->id;
        }

        return $this->repository->all($filters);
    }

    public function getTask(int $id, User $user): ?Task
    {
        $task = $this->repository->find($id);

        if (!$task) {
            return null;
        }

        if ($user->isUser() && $task->assigned_to !== $user->id) {
            throw new \Exception('Unauthorized. You can only view tasks assigned to you.');
        }

        return $task;
    }

    public function createTask(array $data, User $creator): Task
    {
        $taskData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'created_by' => $creator->id,
            'status' => 'pending',
        ];

        $task = $this->repository->create($taskData);

        if (isset($data['dependencies']) && !empty($data['dependencies'])) {
            $this->validateDependencies($data['dependencies'], $task->id);
            $task = $this->repository->addDependencies($task, $data['dependencies']);
        }

        return $task;
    }

    public function updateTask(Task $task, array $data): Task
    {
        if (isset($data['status']) && $data['status'] === 'completed') {
            if (!$this->repository->canBeCompleted($task)) {
                throw new \Exception('Cannot complete task. Some dependencies are not yet completed.');
            }
        }

        $updateData = array_filter($data, function ($key) {
            return in_array($key, ['title', 'description', 'status', 'due_date', 'assigned_to']);
        }, ARRAY_FILTER_USE_KEY);

        $task = $this->repository->update($task, $updateData);

        if (isset($data['dependencies'])) {
            $this->validateDependencies($data['dependencies'], $task->id);
            $task->dependencies()->sync($data['dependencies']);
            $task = $task->load(['dependencies']);
        }

        return $task;
    }

    public function updateTaskStatus(Task $task, string $status, User $user): Task
    {
        if ($task->assigned_to !== $user->id) {
            throw new \Exception('Unauthorized. You can only update status of tasks assigned to you.');
        }

        if ($status === 'completed' && !$this->repository->canBeCompleted($task)) {
            throw new \Exception('Cannot complete task. Some dependencies are not yet completed.');
        }

        return $this->repository->update($task, ['status' => $status]);
    }

    public function deleteTask(Task $task): bool
    {
        return $this->repository->delete($task);
    }

    public function addDependencies(Task $task, array $dependencyIds): Task
    {
        $this->validateDependencies($dependencyIds, $task->id);
        return $this->repository->addDependencies($task, $dependencyIds);
    }

    public function removeDependencies(Task $task, array $dependencyIds): Task
    {
        return $this->repository->removeDependencies($task, $dependencyIds);
    }

    protected function validateDependencies(array $dependencyIds, int $currentTaskId): void
    {
        if (in_array($currentTaskId, $dependencyIds)) {
            throw new \Exception('A task cannot depend on itself.');
        }

        foreach ($dependencyIds as $depId) {
            $dependency = Task::find($depId);
            if ($dependency && $dependency->dependencies()->where('task_id', $currentTaskId)->exists()) {
                throw new \Exception('Circular dependency detected.');
            }
        }
    }
}
