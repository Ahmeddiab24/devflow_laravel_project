<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Cache per-user dashboard stats for 2 minutes (Redis practice!)
        $stats = Cache::remember("dashboard:stats:{$user->id}", 120, function () use ($user) {
            $projects = Project::forUser($user);
            $tasks    = Task::whereHas('project', fn($q) => $q->forUser($user));

            return [
                'total_projects'    => $projects->count(),
                'active_projects'   => $projects->clone()->active()->count(),
                'overdue_projects'  => $projects->clone()->overdue()->count(),
                'total_tasks'       => $tasks->count(),
                'my_tasks'          => $tasks->clone()->assignedTo($user)->count(),
                'pending_tasks'     => $tasks->clone()->byStatus('pending')->count(),
                'in_progress_tasks' => $tasks->clone()->byStatus('in_progress')->count(),
                'done_tasks'        => $tasks->clone()->byStatus('done')->count(),
                'overdue_tasks'     => $tasks->clone()->overdue()->count(),
            ];
        });

        $recentProjects = Project::forUser($user)
            ->with(['owner', 'tasks'])
            ->latest()
            ->take(5)
            ->get();

        $myTasks = Task::with(['project', 'assignee'])
            ->assignedTo($user)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $recentActivity = \Spatie\Activitylog\Models\Activity::causedBy($user)
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', compact('stats', 'recentProjects', 'myTasks', 'recentActivity'));
    }
}
