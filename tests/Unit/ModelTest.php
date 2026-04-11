<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_progress_is_zero_when_no_tasks(): void
    {
        $project = Project::factory()->create();
        $this->assertEquals(0, $project->progress);
    }

    public function test_project_progress_calculates_correctly(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        Task::factory(4)->create(['project_id' => $project->id, 'status' => 'done']);
        Task::factory(1)->create(['project_id' => $project->id, 'status' => 'pending']);

        $project->load('tasks');
        $this->assertEquals(80, $project->progress);
    }

    public function test_is_overdue_returns_true_for_past_due_date(): void
    {
        $project = Project::factory()->create([
            'due_date' => now()->subDay(),
            'status'   => 'active',
        ]);
        $this->assertTrue($project->is_overdue);
    }

    public function test_completed_project_is_not_overdue(): void
    {
        $project = Project::factory()->create([
            'due_date' => now()->subDay(),
            'status'   => 'completed',
        ]);
        $this->assertFalse($project->is_overdue);
    }

    public function test_active_scope_filters_correctly(): void
    {
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'archived']);
        Project::factory()->create(['status' => 'completed']);

        $this->assertEquals(1, Project::active()->count());
    }
}

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_status_color_returns_correct_value(): void
    {
        $task = Task::factory()->make(['status' => 'done']);
        $this->assertEquals('green', $task->status_color);

        $task->status = 'in_progress';
        $this->assertEquals('blue', $task->status_color);
    }

    public function test_task_is_overdue_when_past_due_date(): void
    {
        $task = Task::factory()->make([
            'due_date' => now()->subDays(2),
            'status'   => 'pending',
        ]);
        $this->assertTrue($task->is_overdue);
    }

    public function test_done_task_is_not_overdue(): void
    {
        $task = Task::factory()->make([
            'due_date' => now()->subDays(2),
            'status'   => 'done',
        ]);
        $this->assertFalse($task->is_overdue);
    }
}
