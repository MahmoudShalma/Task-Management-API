<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddTaskDependenciesRequest;
use App\Http\Requests\RemoveTaskDependenciesRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'assigned_to', 'due_date_from', 'due_date_to']);
        $tasks = $this->taskService->getAllTasks($request->user(), $filters);

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated(), $request->user());

        return response()->json([
            'message' => 'Task created successfully',
            'task' => new TaskResource($task),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        try {
            $task = $this->taskService->getTask($id, $request->user());

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found',
                ], 404);
            }

            return new TaskResource($task);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        try {
            $task = $this->taskService->getTask($id, $request->user());

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found',
                ], 404);
            }

            $task = $this->taskService->updateTask($task, $request->validated());

            return response()->json([
                'message' => 'Task updated successfully',
                'task' => new TaskResource($task),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $task = $this->taskService->getTask($id, $request->user());

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found',
                ], 404);
            }

            $validated = $request->validate([
                'status' => ['required', Rule::in(['pending', 'completed', 'canceled'])],
            ]);

            $task = $this->taskService->updateTaskStatus($task, $validated['status'], $request->user());

            return response()->json([
                'message' => 'Task status updated successfully',
                'task' => new TaskResource($task),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $task = $this->taskService->getTask($id, $request->user());

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found',
                ], 404);
            }

            $this->taskService->deleteTask($task);

            return response()->json([
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function addDependencies(AddTaskDependenciesRequest $request, $id)
    {
        try {
            $task = $this->taskService->getTask($id, $request->user());

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found',
                ], 404);
            }

            $task = $this->taskService->addDependencies($task, $request->dependencies);

            return response()->json([
                'message' => 'Dependencies added successfully',
                'task' => new TaskResource($task),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function removeDependencies(RemoveTaskDependenciesRequest $request, $id)
    {
        try {
            $task = $this->taskService->getTask($id, $request->user());

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found',
                ], 404);
            }

            $task = $this->taskService->removeDependencies($task, $request->dependencies);

            return response()->json([
                'message' => 'Dependencies removed successfully',
                'task' => new TaskResource($task),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
