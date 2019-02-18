<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\TaskNotification;
use App\Events\TaskFinished;
use App\Events\TaskDeleted;
use App\Events\TaskStartChanged;
use App\Events\TaskEndChanged;

class NotifyAssignees
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  mixed  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof CommentAdded) {
            $event->comment->task->assignees
                ->where('id', '!=', $event->comment->author->id)
                ->each->notify(new TaskNotification([
                    'iniciator_avatar' => $event->comment->author->picture_path,
                    'iniciator_name' => $event->comment->author->name,
                    'action' => "commented on",
                    'task_name' => $event->comment->task->title,
                    'link' => url('tasks', [$event->comment->task->id]),
                ]));
                return;
        }

        if ($event instanceof TaskFinished) {
            $event->task->assignees
                ->where('id', '!=', $event->task->finisher->id)
                ->each->notify(new TaskNotification([
                    'iniciator_avatar' => $event->task->finisher->picture_path,
                    'iniciator_name' => $event->task->finisher->name,
                    'action' => "marked as finished",
                    'task_name' => $event->task->title,
                    'link' => url('tasks', [$event->task->id]),
                ]));
                return;
        }

        if ($event instanceof TaskDeleted) {
            $event->task->assignees
                ->where('id', '!=', $event->task->creator->id)
                ->each->notify(new TaskNotification([
                    'iniciator_avatar' => $event->task->creator->picture_path,
                    'iniciator_name' => $event->task->creator->name,
                    'action' => "deleted task",
                    'task_name' => $event->task->title,
                    'link' => url('tasks', [$event->task->id]),
                ]));
                return;
        }

        if ($event instanceof TaskStartChanged) {
            $event->task->assignees
                ->where('id', '!=', $event->task->creator->id)
                ->each->notify(new TaskNotification([
                    'iniciator_avatar' => $event->task->creator->picture_path,  
                    'iniciator_name' => $event->task->creator->name,
                    'action' => "changed start date of",
                    'task_name' => $event->task->title,
                    'link' => url('tasks', [$event->task->id]),
                ]));
                return;
        }

        if ($event instanceof TaskEndChanged) {
            $event->task->assignees
                ->where('id', '!=', $event->task->creator->id)
                ->each->notify(new TaskNotification([
                    'iniciator_avatar' => $event->task->creator->picture_path,  
                    'iniciator_name' => $event->task->creator->name,
                    'action' => "changed deadline of",
                    'task_name' => $event->task->title,
                    'link' => url('tasks', [$event->task->id]),
                ]));
                return;
        }

    }
}
