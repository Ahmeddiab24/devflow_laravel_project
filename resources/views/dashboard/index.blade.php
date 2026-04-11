@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('topbar-actions')
    <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
@endsection

@section('content')

    {{-- ── STATS GRID ─────────────────────────────────────────────────────── --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Projects</div>
            <div class="stat-value">{{ $stats['total_projects'] }}</div>
            <div class="stat-sub">{{ $stats['active_projects'] }} active</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Done Tasks</div>
            <div class="stat-value">{{ $stats['done_tasks'] }}</div>
            <div class="stat-sub">of {{ $stats['total_tasks'] }} total</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-label">In Progress</div>
            <div class="stat-value">{{ $stats['in_progress_tasks'] }}</div>
            <div class="stat-sub">{{ $stats['pending_tasks'] }} pending</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Overdue Tasks</div>
            <div class="stat-value">{{ $stats['overdue_tasks'] }}</div>
            <div class="stat-sub">requires attention</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">My Tasks</div>
            <div class="stat-value">{{ $stats['my_tasks'] }}</div>
            <div class="stat-sub">assigned to me</div>
        </div>
    </div>

    {{-- ── MAIN CONTENT ────────────────────────────────────────────────────── --}}
    <div class="grid-2" style="gap:24px">

        {{-- RECENT PROJECTS --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Projects</span>
                <a href="{{ route('projects.index') }}" class="btn btn-ghost btn-sm">View all →</a>
            </div>
            @forelse($recentProjects as $project)
                <div style="padding: 12px 0; border-bottom: 1px solid var(--border);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $project->color }};flex-shrink:0"></div>
                            <a href="{{ route('projects.show', $project) }}" style="font-weight:600;color:var(--text);text-decoration:none;font-size:13.5px">
                                {{ $project->name }}
                            </a>
                        </div>
                        <span class="badge badge-{{ $project->status === 'active' ? 'green' : ($project->status === 'on_hold' ? 'yellow' : 'gray') }}">
                            {{ str_replace('_', ' ', $project->status) }}
                        </span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar {{ $project->progress > 80 ? 'green' : ($project->progress < 30 ? 'yellow' : '') }}"
                             style="width: {{ $project->progress }}%"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:5px">
                        <span style="font-size:11px;color:var(--muted)">{{ $project->tasks->count() }} tasks</span>
                        <span style="font-size:11px;color:var(--muted)">{{ $project->progress }}%</span>
                    </div>
                </div>
            @empty
                <p style="color:var(--muted);text-align:center;padding:20px">No projects yet. <a href="{{ route('projects.create') }}" style="color:var(--accent)">Create one →</a></p>
            @endforelse
        </div>

        {{-- MY TASKS --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">My Tasks</span>
                <span style="font-size:12px;color:var(--muted)">{{ $myTasks->count() }} open</span>
            </div>
            @forelse($myTasks as $task)
                <div style="padding:10px 0;border-bottom:1px solid var(--border)">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" style="font-size:13.5px;font-weight:600;color:var(--text);text-decoration:none">
                                {{ $task->title }}
                            </a>
                            <div style="font-size:11px;color:var(--muted);margin-top:2px">
                                {{ $task->project->name }}
                                @if($task->due_date)
                                    · Due {{ $task->due_date->format('M d') }}
                                    @if($task->is_overdue) <span style="color:var(--danger)">⚠ overdue</span> @endif
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;gap:4px;flex-shrink:0">
                            <span class="badge badge-{{ $task->status_color }}">{{ str_replace('_', ' ', $task->status) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:var(--muted);text-align:center;padding:20px">No tasks assigned to you. 🎉</p>
            @endforelse
        </div>
    </div>

    {{-- ── DEVOPS INFO BANNER ──────────────────────────────────────────────── --}}
    <div style="margin-top:24px;background:rgba(108,99,255,0.08);border:1px solid rgba(108,99,255,0.2);border-radius:12px;padding:20px">
        <div style="font-family:var(--display);font-size:15px;font-weight:700;margin-bottom:12px;color:var(--accent)">
            ⚡ DevOps Practice Stack
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
            @foreach([
                ['🐳 Docker', 'docker compose ps', '#'],
                ['🔄 Queue Worker', 'docker compose logs -f worker', '#'],
                ['📊 Prometheus', 'localhost:9090', 'http://localhost:9090'],
                ['📈 Grafana', 'localhost:3000', 'http://localhost:3000'],
                ['📬 Mailpit', 'localhost:8025', 'http://localhost:8025'],
                ['💚 Health', '/health endpoint', route('health')],
            ] as [$tool, $info, $link])
            <a href="{{ $link }}" target="{{ $link !== '#' ? '_blank' : '_self' }}"
               style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:12px;text-decoration:none;transition:border-color .15s"
               onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $tool }}</div>
                <div style="font-size:11px;color:var(--muted);font-family:var(--mono);margin-top:2px">{{ $info }}</div>
            </a>
            @endforeach
        </div>
    </div>

@endsection
