<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|─────────────────────────────────────────────────────────────────────────────
| DevFlow REST API
|─────────────────────────────────────────────────────────────────────────────
| Auth: Laravel Sanctum (Bearer token)
| Versioned at /api/v1/
|
| Rate Limits: configured in RouteServiceProvider
|   - Auth endpoints:    5 req/min
|   - API endpoints:    60 req/min
*/

// ── AUTH ──────────────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout',  [AuthController::class, 'logout']);
            Route::get('/me',       [AuthController::class, 'me']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });

    // ── PROTECTED ENDPOINTS ──────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::get('/projects/{project}/stats',   [ProjectController::class, 'stats']);
        Route::post('/projects/{project}/archive',[ProjectController::class, 'archive']);

        // Tasks
        Route::apiResource('projects.tasks', TaskController::class)->shallow();
        Route::patch('/tasks/{task}/status',  [TaskController::class, 'updateStatus']);
        Route::patch('/tasks/{task}/assign',  [TaskController::class, 'assign']);
        Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment']);

        // Users
        Route::get('/users',         [UserController::class, 'index']);
        Route::get('/users/{user}',  [UserController::class, 'show']);
        Route::put('/users/{user}',  [UserController::class, 'update']);

        // Dashboard
        Route::get('/dashboard/stats', fn() => response()->json([
            'projects' => \App\Models\Project::where('owner_id', auth()->id())->count(),
            'tasks'    => \App\Models\Task::whereHas('project', fn($q) =>
                              $q->where('owner_id', auth()->id())
                          )->count(),
            'pending'  => \App\Models\Task::whereHas('project', fn($q) =>
                              $q->where('owner_id', auth()->id())
                          )->where('status', 'pending')->count(),
        ]));
    });
});
