<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;
    public int $backoff = 15;

    public function __construct(
        public readonly Task   $task,
        public readonly string $event,
    ) {}

    public function handle(): void
    {
        $task = $this->task->load(['project', 'assignee', 'reporter']);

        Log::info("Task notification", [
            'task_id'  => $task->id,
            'task'     => $task->title,
            'event'    => $this->event,
            'assignee' => $task->assignee?->email,
        ]);

        // Notify assignee when task is assigned or status changed
        if ($task->assignee && in_array($this->event, ['created', 'status_changed'])) {
            // Mail::to($task->assignee->email)->send(new TaskNotificationMail($task, $this->event));
            Log::info("Would email {$task->assignee->email} about task '{$task->title}' ({$this->event})");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendTaskNotification failed", [
            'task_id' => $this->task->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
