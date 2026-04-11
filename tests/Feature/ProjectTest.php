<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── AUTH GUARD ────────────────────────────────────────────────────────────
    public function test_guests_cannot_view_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────
    public function test_authenticated_user_can_view_projects(): void
    {
        $this->actingAs($this->user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertViewIs('projects.index');
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    public function test_user_can_create_project(): void
    {
        $this->actingAs($this->user)
            ->post(route('projects.store'), [
                'name'     => 'Test Project',
                'status'   => 'active',
                'priority' => 'high',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name'     => 'Test Project',
            'owner_id' => $this->user->id,
        ]);
    }

    public function test_project_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('projects.store'), ['status' => 'active', 'priority' => 'medium'])
            ->assertSessionHasErrors('name');
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────
    public function test_owner_can_view_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);
        $project->members()->attach($this->user->id, ['role' => 'owner']);

        $this->actingAs($this->user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertViewIs('projects.show');
    }

    public function test_non_member_cannot_view_project(): void
    {
        $other = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $other->id]);

        $this->actingAs($this->user)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────
    public function test_owner_can_update_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->put(route('projects.update', $project), [
                'name'     => 'Updated Name',
                'status'   => 'on_hold',
                'priority' => 'low',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_non_owner_cannot_update_project(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->put(route('projects.update', $project), ['name' => 'Hacked'])
            ->assertForbidden();
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    public function test_owner_can_delete_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    // ── HEALTH CHECK ──────────────────────────────────────────────────────────
    public function test_health_endpoint_returns_ok(): void
    {
        $this->get(route('health'))
            ->assertOk()
            ->assertJsonStructure(['status', 'app', 'database', 'redis']);
    }
}
