<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// GUEST ROUTES
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/',         fn() => redirect()->route('login'));
    Route::get('/login',    [LoginController::class,    'showForm'])->name('login');
    Route::post('/login',   [LoginController::class,    'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
});

// ─────────────────────────────────────────────────────────────────────────────
// AUTHENTICATED ROUTES
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects (full CRUD)
    Route::resource('projects', ProjectController::class);

    // Tasks (nested under projects + standalone)
    Route::resource('projects.tasks', TaskController::class)->shallow();
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');

    // Users / Team
    Route::get('/team',              [UserController::class, 'index'])->name('team.index');
    Route::get('/profile',           [UserController::class, 'profile'])->name('profile');
    Route::put('/profile',           [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password',  [UserController::class, 'updatePassword'])->name('profile.password');
});

// ─────────────────────────────────────────────────────────────────────────────
// HEALTH CHECK (used by Docker, K8s, load balancers)
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/health', function () {
    try {
        \DB::connection()->getPdo();
        $dbStatus = 'ok';
    } catch (\Exception $e) {
        $dbStatus = 'error';
    }

    try {
        \Cache::store('redis')->put('health_check', true, 10);
        $redisStatus = 'ok';
    } catch (\Exception $e) {
        $redisStatus = 'error';
    }

    $status = ($dbStatus === 'ok' && $redisStatus === 'ok') ? 200 : 503;

    return response()->json([
        'status'  => $status === 200 ? 'healthy' : 'degraded',
        'app'     => 'ok',
        'database'=> $dbStatus,
        'redis'   => $redisStatus,
        'version' => config('app.version', '1.0.0'),
    ], $status);
})->name('health');
