<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Jobs\SendProjectNotification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::forUser($request->user())
            ->with(['owner', 'members', 'tasks'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('projects.create', compact('users'));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        // Add owner as a member
        $project->members()->attach($request->user()->id, ['role' => 'owner']);

        // Add selected members
        if ($request->members) {
            foreach ($request->members as $memberId) {
                $project->members()->syncWithoutDetaching([$memberId => ['role' => 'member']]);
            }
        }

        // Dispatch background job (Queue practice!)
        SendProjectNotification::dispatch($project, 'created');

        // Invalidate dashboard cache
        Cache::forget("dashboard:stats:{$request->user()->id}");

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully!');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['owner', 'members', 'tasks.assignee', 'tasks.reporter']);

        $tasksByStatus = $project->tasks->groupBy('status');

        $stats = [
            'total'       => $project->tasks->count(),
            'pending'     => $project->tasks->where('status', 'pending')->count(),
            'in_progress' => $project->tasks->where('status', 'in_progress')->count(),
            'in_review'   => $project->tasks->where('status', 'in_review')->count(),
            'done'        => $project->tasks->where('status', 'done')->count(),
            'overdue'     => $project->tasks->filter->is_overdue->count(),
            'progress'    => $project->progress,
        ];

        return view('projects.show', compact('project', 'tasksByStatus', 'stats'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $users = User::orderBy('name')->get();
        return view('projects.edit', compact('project', 'users'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        Cache::forget("dashboard:stats:{$request->user()->id}");
        return redirect()->route('projects.show', $project)->with('success', 'Project updated!');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        Cache::forget("dashboard:stats:{auth()->id()}");
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
