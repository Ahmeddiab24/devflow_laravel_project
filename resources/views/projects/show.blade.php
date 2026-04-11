@extends('layouts.app')

@section('page-title', $project->name)
@section('page-subtitle', 'Project · ' . ucfirst($project->status))

@section('topbar-actions')
    <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">+ Add Task</a>
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-ghost">Edit</a>
    <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
@endsection

@section('content')

    {{-- STATS --}}
    <div class="stats-grid" style="margin-bottom:24px">
        <div class="stat-card">
            <div class="stat-label">Total Tasks</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Done</div>
            <div class="stat-value">{{ $stats['done'] }}</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-label">In Progress</div>
            <div class="stat-value">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Overdue</div>
            <div class="stat-value">{{ $stats['overdue'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Progress</div>
            <div class="stat-value">{{ $stats['progress'] }}%</div>
            <div style="margin-top:8px"><div class="progress"><div class="progress-bar" style="width:{{ $stats['progress'] }}%"></div></div></div>
        </div>
    </div>

    {{-- PROJECT META --}}
    <div class="card" style="margin-bottom:24px">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px">
            <div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Owner</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="avatar" style="width:24px;height:24px;font-size:10px">{{ strtoupper(substr($project->owner->name, 0, 2)) }}</div>
                    <span style="font-size:13px;font-weight:600">{{ $project->owner->name }}</span>
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Priority</div>
                <span class="badge badge-{{ $project->priority === 'critical' ? 'red' : ($project->priority === 'high' ? 'yellow' : 'gray') }}">
                    {{ ucfirst($project->priority) }}
                </span>
            </div>
            @if($project->due_date)
            <div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Due Date</div>
                <span style="font-size:13px;{{ $project->is_overdue ? 'color:var(--danger)' : '' }}">
                    {{ $project->due_date->format('M d, Y') }}
                </span>
            </div>
            @endif
            <div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Members</div>
                <div style="display:flex;gap:-4px">
                    @foreach($project->members->take(5) as $member)
                        <div class="avatar" style="width:24px;height:24px;font-size:10px;border:2px solid var(--surface)" title="{{ $member->name }}">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                    @endforeach
                    @if($project->members->count() > 5)
                        <div class="avatar" style="width:24px;height:24px;font-size:10px;border:2px solid var(--surface);background:var(--surface2)">
                            +{{ $project->members->count() - 5 }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if($project->description)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);font-size:13.5px;color:var(--muted)">
                {{ $project->description }}
            </div>
        @endif
    </div>

    {{-- KANBAN BOARD --}}
    <div class="card-header" style="margin-bottom:16px">
        <span class="card-title" style="font-size:16px">📋 Kanban Board</span>
    </div>

    <div class="kanban">
        @foreach(['pending' => '⏳ Pending', 'in_progress' => '🔄 In Progress', 'in_review' => '🔍 In Review', 'done' => '✅ Done', 'cancelled' => '❌ Cancelled'] as $status => $label)
            <div class="kanban-col">
                <div class="kanban-col-header">
                    <span>{{ $label }}</span>
                    <span style="background:var(--surface);padding:1px 7px;border-radius:10px;font-size:11px">
                        {{ $tasksByStatus->get($status, collect())->count() }}
                    </span>
                </div>

                @foreach($tasksByStatus->get($status, collect()) as $task)
                    <a href="{{ route('tasks.show', $task) }}" style="text-decoration:none">
                        <div class="kanban-card">
                            <div class="kanban-card-title">{{ $task->title }}</div>
                            @if($task->labels)
                                <div style="display:flex;flex-wrap:wrap;gap:3px;margin-bottom:8px">
                                    @foreach($task->labels as $label)
                                        <span class="badge badge-purple" style="font-size:10px;padding:1px 6px">{{ $label }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="kanban-card-meta">
                                <span class="badge badge-{{ $task->priority_color }}" style="font-size:10px">{{ $task->priority }}</span>
                                <div style="display:flex;align-items:center;gap:6px">
                                    @if($task->due_date)
                                        <span style="font-size:10px;color:{{ $task->is_overdue ? 'var(--danger)' : 'var(--muted)' }}">
                                            {{ $task->due_date->format('M d') }}
                                        </span>
                                    @endif
                                    @if($task->assignee)
                                        <div class="avatar" style="width:20px;height:20px;font-size:9px" title="{{ $task->assignee->name }}">
                                            {{ strtoupper(substr($task->assignee->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach

                <a href="{{ route('projects.tasks.create', $project) }}?status={{ $status }}"
                   style="display:block;text-align:center;padding:8px;border:1px dashed var(--border);border-radius:8px;color:var(--muted);font-size:12px;text-decoration:none;margin-top:4px"
                   onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                   onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
                    + Add task
                </a>
            </div>
        @endforeach
    </div>

@endsection
