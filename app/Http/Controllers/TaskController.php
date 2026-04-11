<?php

namespace App\Http\Controllers;

use App\Jobs\SendTaskNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create(Project $project)
    {
        $this->authorize('view', $project);
        $users = $project->members()->with('user')->get()->pluck(null, 'id');
        $allUsers = User::orderBy('name')->get();
        return view('tasks.create', compact('project', 'allUsers'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'status'          => 'required|in:' . implode(',', Task::STATUSES),
            'priority'        => 'required|in:' . implode(',', Task::PRIORITIES),
            'assignee_id'     => 'nullable|exists:users,id',
            'due_date'        => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'labels'          => 'nullable|array',
        ]);

        $task = $project->tasks()->create([
            ...$data,
            'reporter_id' => auth()->id(),
        ]);

        SendTaskNotification::dispatch($task, 'created');

        if ($request->wantsJson()) {
            return response()->json(['task' => $task->load(['assignee', 'reporter'])], 201);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Task created!');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task->project);
        $task->load(['project', 'assignee', 'reporter', 'comments.user']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $users = User::orderBy('name')->get();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'status'          => 'required|in:' . implode(',', Task::STATUSES),
            'priority'        => 'required|in:' . implode(',', Task::PRIORITIES),
            'assignee_id'     => 'nullable|exists:users,id',
            'due_date'        => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'logged_hours'    => 'nullable|numeric|min:0',
            'labels'          => 'nullable|array',
        ]);

        $task->update($data);
        return redirect()->route('tasks.show', $task)->with('success', 'Task updated!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $project = $task->project;
        $task->delete();
        return redirect()->route('projects.show', $project)->with('success', 'Task deleted.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate(['status' => 'required|in:' . implode(',', Task::STATUSES)]);
        $task->update(['status' => $request->status]);
        SendTaskNotification::dispatch($task, 'status_changed');
        return response()->json(['task' => $task]);
    }

    public function assign(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate(['assignee_id' => 'nullable|exists:users,id']);
        $task->update(['assignee_id' => $request->assignee_id]);
        return response()->json(['task' => $task->load('assignee')]);
    }

    public function addComment(Request $request, Task $task)
    {
        $request->validate(['body' => 'required|string|max:2000']);
        $comment = $task->comments()->create([
            'body'    => $request->body,
            'user_id' => auth()->id(),
        ]);
        return response()->json(['comment' => $comment->load('user')], 201);
    }
}
