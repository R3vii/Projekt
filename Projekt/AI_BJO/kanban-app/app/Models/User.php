<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'specialization',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isLeaderOrAdmin(): bool
    {
        return in_array($this->role, ['admin', 'leader']);
    }

    // Check if leader has access to given specialization category
    public function canManageCategory(string $category): bool
    {
        if ($this->isAdmin()) return true;
        if ($this->isLeader() && $this->specialization === $category) return true;
        return false;
    }

    // Relationships
    public function projects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withTimestamps();
    }
    public function tasks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
                    ->withPivot('assigned_at')
                    ->withTimestamps();
    }

    public function createdTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function uploadedFiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskFile::class, 'uploaded_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeBySpecialization($query, string $spec)
    {
        return $query->where('specialization', $spec);
    }

    // Helpers
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator (CEO)',
            'leader' => 'Lider',
            'user' => 'Użytkownik',
            default => 'Nieznany',
        };
    }

    public function getSpecializationLabelAttribute(): string
    {
        return match ($this->specialization) {
            'writers' => 'Writerzy',
            'graphics' => 'Graficy',
            'programmers' => 'Programiści',
            default => '—',
        };
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }
}
