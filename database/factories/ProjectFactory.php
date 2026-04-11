<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'status'      => $this->faker->randomElement(['active', 'on_hold', 'completed']),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'owner_id'    => User::factory(),
            'due_date'    => $this->faker->optional()->dateTimeBetween('now', '+90 days'),
            'color'       => $this->faker->hexColor(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date' => now()->subDays(5),
            'status'   => 'active',
        ]);
    }
}
