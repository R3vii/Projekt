<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
        ], [
            'content.required' => 'Komentarz nie może być pusty.',
            'content.max' => 'Komentarz nie może przekraczać 2000 znaków.',
        ]);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Notify relevant users
        $usersToNotify = collect();
        $usersToNotify = $usersToNotify->merge($task->assignees);
        
        $leadersAndAdmins = \App\Models\User::whereIn('role', ['admin', 'leader'])->get();
        foreach ($leadersAndAdmins as $u) {
            if ($u->isAdmin() || ($u->isLeader() && $u->canManageCategory($task->getStatusCategory()))) {
                $usersToNotify->push($u);
            }
        }

        $usersToNotify = $usersToNotify->unique('id')->reject(fn($u) => $u->id === Auth::id());
        \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\NewCommentNotification($comment));

        return back()->with('success', 'Komentarz został dodany.');
    }

    public function destroy(Task $task, Comment $comment)
    {
        // Only author or admin can delete
        if ($comment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Brak uprawnień do usunięcia tego komentarza.');
        }

        $comment->delete();
        return back()->with('success', 'Komentarz został usunięty.');
    }
}
