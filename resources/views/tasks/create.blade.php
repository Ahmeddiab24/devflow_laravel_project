@extends('layouts.app')
@section('page-title', 'New Task')
@section('page-subtitle', $project->name)

@section('content')
<div style="max-width:640px">
    <div class="card">
        <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Task Title *</label>
                <input type="text" name="title" class="form-control"
                       value="{{ old('title') }}"
                       placeholder="e.g. Set up Prometheus monitoring"
                       required autofocus>
                @error('title')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Describe the task…" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        @foreach(\App\Models\Task::STATUSES as $s)
                            <option value="{{ $s }}"
                                {{ old('status', request('status', 'pending')) === $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control">
                        @foreach(\App\Models\Task::PRIORITIES as $p)
                            <option value="{{ $p }}" {{ old('priority', 'medium') === $p ? 'selected' : '' }}>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Assignee</label>
                    <select name="assignee_id" class="form-control">
                        <option value="">Unassigned</option>
                        @foreach($allUsers as $user)
                            <option value="{{ $user->id }}" {{ old('assignee_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Estimated Hours</label>
                    <input type="number" name="estimated_hours" class="form-control"
                           value="{{ old('estimated_hours') }}" placeholder="e.g. 4" min="0" step="0.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Labels (comma-separated)</label>
                    <input type="text" name="labels_input" class="form-control"
                           value="{{ old('labels_input') }}" placeholder="backend, devops, bug">
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>
@endsection
