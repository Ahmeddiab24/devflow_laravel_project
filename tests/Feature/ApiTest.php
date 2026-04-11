<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    // ── AUTH ──────────────────────────────────────────────────────────────────
    public function test_user_can_login_via_api(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])
        ->assertOk()
        ->assertJsonStructure(['user', 'token']);
    }

    public function test_invalid_credentials_return_422(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertUnprocessable();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/projects')->assertUnauthorized();
    }

    // ── PROJECTS API ──────────────────────────────────────────────────────────
    public function test_authenticated_user_can_list_projects(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Project::factory(3)->create(['owner_id' => $user->id])->each(function ($p) use ($user) {
            $p->members()->attach($user->id, ['role' => 'owner']);
        });

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonStructure(['data', 'total', 'per_page']);
    }

    public function test_user_can_create_project_via_api(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/projects', [
            'name'     => 'API Project',
            'status'   => 'active',
            'priority' => 'medium',
        ])
        ->assertCreated()
        ->assertJsonPath('project.name', 'API Project');
    }

    public function test_project_stats_endpoint(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id, ['role' => 'owner']);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/projects/{$project->id}/stats")
            ->assertOk()
            ->assertJsonStructure(['progress', 'total_tasks', 'by_status', 'by_priority', 'overdue']);
    }

    public function test_dashboard_stats_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/dashboard/stats')
            ->assertOk()
            ->assertJsonStructure(['projects', 'tasks', 'pending']);
    }

    // ── AUTH ME ───────────────────────────────────────────────────────────────
    public function test_me_endpoint_returns_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }
}
