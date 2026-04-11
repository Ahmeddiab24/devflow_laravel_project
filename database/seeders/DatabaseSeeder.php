<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ────────────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@devflow.local',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $members = collect([
            ['name' => 'Alice Chen',    'email' => 'alice@devflow.local'],
            ['name' => 'Bob Smith',     'email' => 'bob@devflow.local'],
            ['name' => 'Carlos Rivera', 'email' => 'carlos@devflow.local'],
            ['name' => 'Diana Nguyen',  'email' => 'diana@devflow.local'],
        ])->map(fn($u) => User::create([
            ...$u,
            'password' => Hash::make('password'),
            'role'     => 'member',
        ]));

        $allUsers = $members->prepend($admin);

        // ── PROJECTS ─────────────────────────────────────────────────────────
        $projects = [
            ['name' => 'DevFlow Platform',        'color' => '#6366f1', 'status' => 'active',    'priority' => 'high'],
            ['name' => 'API Gateway Migration',    'color' => '#f59e0b', 'status' => 'active',    'priority' => 'critical'],
            ['name' => 'Mobile App v2',            'color' => '#10b981', 'status' => 'on_hold',   'priority' => 'medium'],
            ['name' => 'Infrastructure Overhaul',  'color' => '#ef4444', 'status' => 'active',    'priority' => 'high'],
            ['name' => 'Analytics Dashboard',      'color' => '#3b82f6', 'status' => 'completed', 'priority' => 'low'],
        ];

        foreach ($projects as $projectData) {
            $project = Project::create([
                ...$projectData,
                'description' => "This is the {$projectData['name']} project. Used for DevOps practice.",
                'owner_id'    => $admin->id,
                'due_date'    => now()->addDays(rand(10, 90)),
            ]);

            // Add members
            $project->members()->attach($admin->id, ['role' => 'owner']);
            $members->random(rand(2, 4))->each(function ($member) use ($project) {
                $project->members()->syncWithoutDetaching([$member->id => ['role' => 'member']]);
            });

            // Add tasks
            $statuses  = Task::STATUSES;
            $priorities = Task::PRIORITIES;

            for ($i = 0; $i < rand(8, 15); $i++) {
                $task = $project->tasks()->create([
                    'title'           => $this->taskTitle(),
                    'description'     => 'Task description for DevOps practice app.',
                    'status'          => $statuses[array_rand($statuses)],
                    'priority'        => $priorities[array_rand($priorities)],
                    'assignee_id'     => $allUsers->random()->id,
                    'reporter_id'     => $admin->id,
                    'due_date'        => now()->addDays(rand(-5, 30)),
                    'estimated_hours' => rand(1, 16),
                    'logged_hours'    => rand(0, 10),
                    'labels'          => $this->randomLabels(),
                ]);

                // Add comments to some tasks
                if (rand(0, 1)) {
                    for ($j = 0; $j < rand(1, 3); $j++) {
                        $task->comments()->create([
                            'body'    => $this->commentBody(),
                            'user_id' => $allUsers->random()->id,
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Seeded: 5 users, 5 projects, ~60 tasks with comments');
        $this->command->info('   Login: admin@devflow.local / password');
    }

    private function taskTitle(): string
    {
        $tasks = [
            'Set up Docker containerization',
            'Configure Nginx reverse proxy',
            'Write CI/CD pipeline',
            'Set up Prometheus monitoring',
            'Create Grafana dashboards',
            'Implement Redis caching',
            'Configure MySQL replication',
            'Write unit tests',
            'Deploy to Kubernetes',
            'Set up SSL certificates',
            'Implement rate limiting',
            'Configure log aggregation',
            'Set up alerting rules',
            'Optimize database queries',
            'Implement health checks',
            'Configure auto-scaling',
            'Set up backup strategy',
            'Security hardening',
            'Performance testing',
            'Document API endpoints',
        ];
        return $tasks[array_rand($tasks)];
    }

    private function randomLabels(): array
    {
        $labels = ['backend', 'frontend', 'devops', 'bug', 'feature', 'security', 'performance', 'docs'];
        return array_values(array_intersect_key($labels, array_flip(array_rand($labels, rand(1, 3)))));
    }

    private function commentBody(): string
    {
        $comments = [
            'Looking good! Left a few inline suggestions.',
            'This needs more testing before merging.',
            'I can take this one. Will start today.',
            'Blocked by the infrastructure ticket. Waiting on @alice.',
            'Done. Deployed to staging, all checks passing.',
            'Can we add monitoring for this? It would help catch issues early.',
            'Redis cache is reducing response time by 40%. 🚀',
        ];
        return $comments[array_rand($comments)];
    }
}
