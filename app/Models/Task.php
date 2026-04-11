<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'project_id',
        'assignee_id',
        'reporter_id',
        'due_date',
        'estimated_hours',
        'logged_hours',
        'labels',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'labels'         => 'array',
        'estimated_hours'=> 'float',
        'logged_hours'   => 'float',
    ];

    // ── STATUS CONSTANTS ─────────────────────────────────────────────────────
    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_IN_REVIEW   = 'in_review';
    const STATUS_DONE        = 'done';
    const STATUS_CANCELLED   = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_IN_REVIEW,
        self::STATUS_DONE,
        self::STATUS_CANCELLED,
    ];

    const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    // ── RELATIONS ────────────────────────────────────────────────────────────
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    // ── SCOPES ───────────────────────────────────────────────────────────────
    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assignee_id', $user->id);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereNotIn('status', [self::STATUS_DONE, self::STATUS_CANCELLED]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── COMPUTED ATTRIBUTES ──────────────────────────────────────────────────
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast()
            && !in_array($this->status, [self::STATUS_DONE, self::STATUS_CANCELLED]);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_IN_REVIEW   => 'yellow',
            self::STATUS_DONE        => 'green',
            self::STATUS_CANCELLED   => 'red',
            default                  => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'yellow',
            'low'      => 'green',
            default    => 'gray',
        };
    }

    // ── ACTIVITY LOG ─────────────────────────────────────────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Task {$eventName}");
    }
}
