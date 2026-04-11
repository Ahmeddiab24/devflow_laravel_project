<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::forUser($request->user())
            ->with(['owner', 'members'])
            ->withCount('tasks')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate(15);

        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:' . implode(',', Project::STATUSES),
            'priority'    => 'required|in:' . implode(',', Project::PRIORITIES),
            'due_date'    => 'nullable|date',
            'color'       => 'nullable|string|size:7',
        ]);

        $project = Project::create([...$data, 'owner_id' => $request->user()->id]);
        $project->members()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json(['project' => $project->load('owner')], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        return response()->json([
            'project' => $project->load(['owner', 'members', 'tasks.assignee']),
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'sometimes|in:' . implode(',', Project::STATUSES),
            'priority'    => 'sometimes|in:' . implode(',', Project::PRIORITIES),
            'due_date'    => 'nullable|date',
            'color'       => 'nullable|string|size:7',
        ]);

        $project->update($data);
        return response()->json(['project' => $project]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(null, 204);
    }

    public function stats(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        return response()->json([
            'progress'    => $project->progress,
            'total_tasks' => $project->tasks()->count(),
            'by_status'   => $project->tasks()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_priority' => $project->tasks()->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'overdue'     => $project->tasks()->overdue()->count(),
        ]);
    }

    public function archive(Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project->update(['status' => Project::STATUS_ARCHIVED]);
        return response()->json(['project' => $project]);
    }
}
