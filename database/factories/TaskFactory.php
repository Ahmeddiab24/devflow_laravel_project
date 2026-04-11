<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title'           => $this->faker->sentence(4),
            'description'     => $this->faker->paragraph(),
            'status'          => $this->faker->randomElement(Task::STATUSES),
            'priority'        => $this->faker->randomElement(Task::PRIORITIES),
            'project_id'      => Project::factory(),
            'assignee_id'     => null,
            'reporter_id'     => null,
            'due_date'        => $this->faker->optional()->dateTimeBetween('now', '+60 days'),
            'estimated_hours' => $this->faker->optional()->randomFloat(1, 1, 20),
            'logged_hours'    => 0,
            'labels'          => $this->faker->optional()->randomElements(['backend','frontend','devops','bug','feature'], 2),
        ];
    }

    public function done(): static
    {
        return $this->state(['status' => 'done']);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date' => now()->subDays(3),
            'status'   => 'pending',
        ]);
    }
}

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => 'member',
            'timezone'          => 'UTC',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
