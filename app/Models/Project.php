<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'owner_id',
        'due_date',
        'color',
    ];

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── STATUS CONSTANTS ─────────────────────────────────────────────────────
    const STATUS_ACTIVE    = 'active';
    const STATUS_ON_HOLD   = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED  = 'archived';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_ON_HOLD,
        self::STATUS_COMPLETED,
        self::STATUS_ARCHIVED,
    ];

    const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    // ── RELATIONS ────────────────────────────────────────────────────────────
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // ── SCOPES ───────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
        });
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_ARCHIVED]);
    }

    // ── COMPUTED ATTRIBUTES ──────────────────────────────────────────────────
    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast()
            && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_ARCHIVED]);
    }

    // ── ACTIVITY LOG ─────────────────────────────────────────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Project {$eventName}");
    }
}
