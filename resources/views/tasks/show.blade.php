@extends('layouts.app')
@section('page-title', $task->title)
@section('page-subtitle', $task->project->name . ' · ' . ucfirst(str_replace('_', ' ', $task->status)))

@section('topbar-actions')
    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-ghost">Edit</a>
    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
@endsection

@section('content')
<div class="grid-2" style="align-items:start">

    {{-- TASK DETAILS --}}
    <div>
        <div class="card" style="margin-bottom:20px">
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
                <span class="badge badge-{{ $task->status_color }}">{{ str_replace('_',' ',$task->status) }}</span>
                <span class="badge badge-{{ $task->priority_color }}">{{ $task->priority }}</span>
                @foreach($task->labels ?? [] as $label)
                    <span class="badge badge-purple">{{ $label }}</span>
                @endforeach
            </div>

            @if($task->description)
                <p style="color:var(--muted);font-size:13.5px;line-height:1.7;margin-bottom:20px">{{ $task->description }}</p>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding-top:16px;border-top:1px solid var(--border)">
                <div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Assignee</div>
                    @if($task->assignee)
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="avatar" style="width:24px;height:24px;font-size:10px">{{ strtoupper(substr($task->assignee->name,0,2)) }}</div>
                            <span style="font-size:13px">{{ $task->assignee->name }}</span>
                        </div>
                    @else
                        <span style="color:var(--muted);font-size:13px">Unassigned</span>
                    @endif
                </div>
                <div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Reporter</div>
                    @if($task->reporter)
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="avatar" style="width:24px;height:24px;font-size:10px">{{ strtoupper(substr($task->reporter->name,0,2)) }}</div>
                            <span style="font-size:13px">{{ $task->reporter->name }}</span>
                        </div>
                    @endif
                </div>
                @if($task->due_date)
                <div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Due Date</div>
                    <span style="font-size:13px;{{ $task->is_overdue ? 'color:var(--danger)' : '' }}">
                        {{ $task->is_overdue ? '⚠ ' : '' }}{{ $task->due_date->format('M d, Y') }}
                    </span>
                </div>
                @endif
                @if($task->estimated_hours)
                <div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Time</div>
                    <span style="font-size:13px;font-family:var(--mono)">
                        {{ $task->logged_hours }}h / {{ $task->estimated_hours }}h
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- QUICK STATUS UPDATE --}}
        <div class="card">
            <div class="card-title" style="margin-bottom:14px">Update Status</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach(['pending','in_progress','in_review','done','cancelled'] as $status)
                    <button onclick="updateStatus('{{ $task->id }}','{{ $status }}')"
                            class="btn {{ $task->status === $status ? 'btn-primary' : 'btn-ghost' }} btn-sm">
                        {{ str_replace('_',' ',$status) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- COMMENTS --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">💬 Comments ({{ $task->comments->count() }})</div>

        @forelse($task->comments as $comment)
            <div style="display:flex;gap:10px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border)">
                <div class="avatar" style="width:28px;height:28px;font-size:10px;flex-shrink:0">
                    {{ strtoupper(substr($comment->user->name,0,2)) }}
                </div>
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span style="font-size:13px;font-weight:600">{{ $comment->user->name }}</span>
                        <span style="font-size:11px;color:var(--muted)">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="font-size:13.5px;color:var(--muted);line-height:1.6">{{ $comment->body }}</p>
                </div>
            </div>
        @empty
            <p style="color:var(--muted);font-size:13px;margin-bottom:16px">No comments yet. Start the conversation!</p>
        @endforelse

        {{-- Add Comment Form --}}
        <div id="comment-form">
            <textarea id="comment-body" class="form-control" placeholder="Add a comment…" style="margin-bottom:10px"></textarea>
            <button onclick="addComment()" class="btn btn-primary btn-sm">Post Comment</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(taskId, status) {
    fetch(`/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status })
    }).then(() => window.location.reload());
}

function addComment() {
    const body = document.getElementById('comment-body').value.trim();
    if (!body) return;

    fetch(`/tasks/{{ $task->id }}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ body })
    }).then(r => r.json()).then(() => window.location.reload());
}
</script>
@endpush
