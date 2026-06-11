<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->comment->task_id,
            'task_title' => $this->comment->task->title,
            'comment_id' => $this->comment->id,
            'author' => $this->comment->user->name,
            'content' => str($this->comment->content)->limit(50)->toString(),
        ];
    }
}
