<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id
            || $project->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id || $user->role === 'admin';
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id || $user->role === 'admin';
    }
}
