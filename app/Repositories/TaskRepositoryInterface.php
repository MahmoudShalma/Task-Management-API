<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function find(int $id): ?Task;

    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): bool;

    public function addDependencies(Task $task, array $dependencyIds): Task;

    public function removeDependencies(Task $task, array $dependencyIds): Task;

    public function canBeCompleted(Task $task): bool;
}
