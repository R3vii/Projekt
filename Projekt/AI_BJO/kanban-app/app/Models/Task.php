<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'description_short',
        'status',
        'priority',
        'created_by',
        'due_date',
        'project_id',
        'profession',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Status constants
    const STATUS_WRITERS = 'writers';
    const STATUS_GRAPHICS = 'graphics';
    const STATUS_PROGRAMMERS = 'programmers';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_TO_APPROVE = 'to_approve';
    const STATUS_DONE = 'done';

    const STATUSES = [
        self::STATUS_WRITERS => 'Writerzy',
        self::STATUS_GRAPHICS => 'Graficy',
        self::STATUS_PROGRAMMERS => 'Programiści',
        self::STATUS_IN_PROGRESS => 'W trakcie',
        self::STATUS_TO_APPROVE => 'Do zatwierdzenia',
        self::STATUS_DONE => 'Zrobione',
    ];

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    const PRIORITIES = [
        self::PRIORITY_LOW => 'Niski',
        self::PRIORITY_MEDIUM => 'Średni',
        self::PRIORITY_HIGH => 'Wysoki',
    ];

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
                    ->withPivot('assigned_at')
                    ->withTimestamps();
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskFile::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->with('user')->latest();
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id));
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Helpers
    public function isAssigned(User $user): bool
    {
        return $this->assignees->contains($user->id);
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isLeader() && $user->canManageCategory($this->getStatusCategory())) return true;
        if ($user->isUser() && $this->isAssigned($user)) return true;
        return false;
    }

    public function getStatusCategory(): string
    {
        return $this->profession ?? '';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'writers' => 'purple',
            'graphics' => 'pink',
            'programmers' => 'blue',
            'in_progress' => 'amber',
            'to_approve' => 'teal',
            'done' => 'green',
            default => 'slate',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'green',
            'medium' => 'amber',
            'high' => 'red',
            default => 'slate',
        };
    }

    public function getStatusLabelFromValue(string $value): string
    {
        return self::STATUSES[$value] ?? $value;
    }

    public function getPendingFilesCountAttribute(): int
    {
        return $this->files()->where('status', 'pending')->count();
    }
}
