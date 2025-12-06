<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Task routes - accessible by all authenticated users
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);

    // User can update status of their assigned tasks
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);

    // Manager-only routes
    Route::middleware('manager')->group(function () {
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{id}', [TaskController::class, 'update']);
        Route::patch('/tasks/{id}', [TaskController::class, 'update']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

        // Task dependencies routes
        Route::post('/tasks/{id}/dependencies', [TaskController::class, 'addDependencies']);
        Route::delete('/tasks/{id}/dependencies', [TaskController::class, 'removeDependencies']);
    });
});
