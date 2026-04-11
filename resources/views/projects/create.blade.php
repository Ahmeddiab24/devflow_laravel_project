@extends('layouts.app')
@section('page-title', 'New Project')
@section('page-subtitle', 'Create a new project')

@section('content')
<div style="max-width:640px">
    <div class="card">
        <form method="POST" action="{{ route('projects.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Project Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. API Gateway Migration" required>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="What is this project about?">{{ old('description') }}</textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        @foreach(['active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control">
                        @foreach(['low','medium','high','critical'] as $p)
                            <option value="{{ $p }}" {{ old('priority','medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" class="form-control" value="{{ old('color', '#6366f1') }}" style="height:42px;padding:4px">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Add Members</label>
                <select name="members[]" class="form-control" multiple style="height:120px">
                    @foreach($users as $user)
                        @if($user->id !== auth()->id())
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endif
                    @endforeach
                </select>
                <div style="font-size:11px;color:var(--muted);margin-top:4px">Hold Ctrl/Cmd to select multiple</div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                <a href="{{ route('projects.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Project</button>
            </div>
        </form>
    </div>
</div>
@endsection
