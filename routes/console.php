<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|──────────────────────────────────────────────────────────────────────────────
| DevFlow — Console Commands & Scheduled Tasks
|
| DevOps practice:
|   The scheduler runs inside the 'scheduler' Docker container.
|   It executes `php artisan schedule:run` every minute via a while loop.
|   Monitor which tasks ran with: docker compose logs -f scheduler
|──────────────────────────────────────────────────────────────────────────────
*/

// ── SCHEDULED TASKS ──────────────────────────────────────────────────────────

// Clear expired cache entries every hour
Schedule::command('cache:prune-stale-tags')->hourly();

// Send daily digest of overdue tasks at 8am UTC
Schedule::command('devflow:overdue-digest')->dailyAt('08:00');

// Clean up soft-deleted records older than 30 days, weekly
Schedule::command('model:prune', ['--model' => [
    \App\Models\Project::class,
    \App\Models\Task::class,
]])->weekly();

// ── CUSTOM ARTISAN COMMANDS ───────────────────────────────────────────────────

Artisan::command('devflow:overdue-digest', function () {
    $overdueTasks = \App\Models\Task::with(['assignee', 'project'])
        ->overdue()
        ->get()
        ->groupBy('assignee_id');

    $count = 0;
    foreach ($overdueTasks as $userId => $tasks) {
        $user = $tasks->first()->assignee;
        if (!$user) continue;

        // In production: Mail::to($user)->send(new OverdueDigestMail($tasks));
        $this->info("Would email {$user->email} about {$tasks->count()} overdue tasks");
        $count++;
    }

    $this->info("Overdue digest sent to {$count} users.");
})->purpose('Send overdue task digest emails to assignees');

Artisan::command('devflow:stats', function () {
    $this->table(
        ['Metric', 'Count'],
        [
            ['Users',    \App\Models\User::count()],
            ['Projects', \App\Models\Project::count()],
            ['Tasks',    \App\Models\Task::count()],
            ['Comments', \App\Models\Comment::count()],
            ['Overdue',  \App\Models\Task::overdue()->count()],
            ['Jobs in queue', \Illuminate\Support\Facades\Redis::llen('queues:default')],
            ['Failed jobs', \DB::table('failed_jobs')->count()],
        ]
    );
})->purpose('Show DevFlow application statistics');

Artisan::command('health:check', function () {
    try {
        \DB::connection()->getPdo();
        \Cache::store('redis')->put('health', true, 10);
        $this->info('OK');
        return 0;
    } catch (\Exception $e) {
        $this->error('FAIL: ' . $e->getMessage());
        return 1;
    }
})->purpose('Health check for Docker/K8s probes');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
