<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Admin and all logged users can view the board listing
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Admin, Leader of the category, assigned users can view full details
    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isLeader() && $user->canManageCategory($task->getStatusCategory())) return true;
        if ($task->isAssigned($user)) return true;
        return false;
    }

    // Admin and Leaders can create tasks
    public function create(User $user): bool
    {
        return $user->isLeaderOrAdmin();
    }

    // Admin can update any task; Leader can update own category; assigned user can update status/upload files
    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isLeader() && $user->canManageCategory($task->getStatusCategory())) return true;
        if ($user->isUser() && $task->isAssigned($user)) return true;
        return false;
    }

    // Only admin can delete
    public function delete(User $user, Task $task): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isLeader() && $user->canManageCategory($task->getStatusCategory())) return true;
        return false;
    }

    // Only assigned users (or leader/admin) can change status
    public function changeStatus(User $user, Task $task): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isLeader() && $user->canManageCategory($task->getStatusCategory())) return true;
        return $task->isAssigned($user);
    }

    // Only assigned users (or leader/admin) can upload files
    public function uploadFile(User $user, Task $task): bool
    {
        if ($user->isLeaderOrAdmin()) return true;
        return $task->isAssigned($user);
    }
}
