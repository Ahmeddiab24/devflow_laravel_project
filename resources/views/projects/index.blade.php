@extends('layouts.app')

@section('page-title', 'Projects')
@section('page-subtitle', $projects->total() . ' projects total')

@section('topbar-actions')
    <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="search" class="form-control" placeholder="Search projects…" value="{{ request('search') }}" style="width:200px">
        <select name="status" class="form-control" onchange="this.form.submit()" style="width:140px">
            <option value="">All statuses</option>
            @foreach(['active','on_hold','completed','archived'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
@endsection

@section('content')

    @if($projects->isEmpty())
        <div style="text-align:center;padding:80px 20px">
            <div style="font-size:48px;margin-bottom:16px">📁</div>
            <div style="font-family:var(--display);font-size:20px;font-weight:700;margin-bottom:8px">No projects yet</div>
            <p style="color:var(--muted);margin-bottom:24px">Create your first project to get started</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
        </div>
    @else
        <div class="grid-auto">
            @foreach($projects as $project)
                <div class="card" style="position:relative;overflow:hidden;border-top:3px solid {{ $project->color }}">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
                        <a href="{{ route('projects.show', $project) }}" style="font-family:var(--display);font-size:15px;font-weight:700;color:var(--text);text-decoration:none">
                            {{ $project->name }}
                        </a>
                        <div style="display:flex;gap:4px">
                            <span class="badge badge-{{ $project->priority === 'critical' ? 'red' : ($project->priority === 'high' ? 'yellow' : 'gray') }}">
                                {{ $project->priority }}
                            </span>
                        </div>
                    </div>

                    @if($project->description)
                        <p style="font-size:12.5px;color:var(--muted);margin-bottom:14px;line-height:1.5">
                            {{ Str::limit($project->description, 80) }}
                        </p>
                    @endif

                    <div style="margin-bottom:12px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                            <span style="font-size:11px;color:var(--muted)">Progress</span>
                            <span style="font-size:11px;font-weight:600">{{ $project->progress }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $project->progress > 80 ? 'green' : '' }}" style="width:{{ $project->progress }}%"></div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="display:flex;align-items:center;gap:6px">
                            <span class="badge badge-{{ $project->status === 'active' ? 'green' : ($project->status === 'on_hold' ? 'yellow' : 'gray') }}">
                                {{ str_replace('_',' ',$project->status) }}
                            </span>
                            <span style="font-size:11px;color:var(--muted)">{{ $project->tasks->count() }} tasks</span>
                        </div>
                        @if($project->due_date)
                            <span style="font-size:11px;color:{{ $project->is_overdue ? 'var(--danger)' : 'var(--muted)' }}">
                                {{ $project->is_overdue ? '⚠ ' : '' }}{{ $project->due_date->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:24px">
            {{ $projects->withQueryString()->links() }}
        </div>
    @endif

@endsection
