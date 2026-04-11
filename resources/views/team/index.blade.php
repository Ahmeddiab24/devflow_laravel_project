@extends('layouts.app')
@section('page-title', 'Team')
@section('page-subtitle', 'All members')

@section('content')

@php $users = \App\Models\User::withCount(['ownedProjects','assignedTasks'])->orderBy('name')->get(); @endphp

<div class="grid-auto">
    @foreach($users as $user)
        <div class="card" style="display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div class="avatar" style="width:44px;height:44px;font-size:16px">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div style="font-weight:700;font-size:15px">{{ $user->name }}</div>
                    <div style="font-size:12px;color:var(--muted)">{{ $user->email }}</div>
                </div>
                <span class="badge badge-{{ $user->role === 'admin' ? 'purple' : 'gray' }}" style="margin-left:auto">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
            <div style="display:flex;gap:16px;padding-top:12px;border-top:1px solid var(--border)">
                <div style="text-align:center">
                    <div style="font-size:20px;font-weight:800;font-family:'Syne',sans-serif">{{ $user->owned_projects_count }}</div>
                    <div style="font-size:11px;color:var(--muted)">Projects</div>
                </div>
                <div style="text-align:center">
                    <div style="font-size:20px;font-weight:800;font-family:'Syne',sans-serif">{{ $user->assigned_tasks_count }}</div>
                    <div style="font-size:11px;color:var(--muted)">Tasks</div>
                </div>
                <div style="margin-left:auto;display:flex;align-items:center">
                    <span style="width:8px;height:8px;border-radius:50%;background:var(--success);display:inline-block;margin-right:6px"></span>
                    <span style="font-size:12px;color:var(--muted)">Active</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
