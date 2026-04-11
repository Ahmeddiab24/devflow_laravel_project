<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * SendProjectNotification
 *
 * This job is dispatched to the Redis queue and processed by the queue worker.
 * It demonstrates: queued jobs, retries, failure handling, and mail notifications.
 *
 * DevOps practice:
 *   - Monitor this queue in Grafana (queue depth metric)
 *   - Observe worker logs: docker compose logs -f worker
 *   - Test failure: throw an exception and watch retry behaviour
 *   - Scale workers: docker compose up --scale worker=4
 */
class SendProjectNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;          // Retry up to 3 times
    public int $timeout = 60;         // Kill job after 60 seconds
    public int $backoff = 30;         // Wait 30s between retries

    public function __construct(
        public readonly Project $project,
        public readonly string  $event,
    ) {}

    public function handle(): void
    {
        $project = $this->project->load(['owner', 'members']);
        $members = $project->members;

        Log::info("Processing project notification", [
            'project_id' => $project->id,
            'event'      => $this->event,
            'members'    => $members->count(),
        ]);

        foreach ($members as $member) {
            // In a real app, send actual emails here
            // Mail::to($member->email)->send(new ProjectNotificationMail($project, $this->event));
            Log::info("Notified {$member->email} about project '{$project->name}' ({$this->event})");
        }
    }

    /**
     * Handle a job failure.
     * DevOps: Failed jobs go to the failed_jobs table.
     * Check with: docker compose exec app php artisan queue:failed
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendProjectNotification failed", [
            'project_id' => $this->project->id,
            'event'      => $this->event,
            'error'      => $exception->getMessage(),
        ]);
    }
}
