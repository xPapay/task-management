<?php

namespace App\Listeners;

use App\Events\UsersAssignedOnTask;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\UserAssignedOnTask;

class NotifyAssignedUsers
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
     * @param  UsersAssignedOnTask  $event
     * @return void
     */
    public function handle(UsersAssignedOnTask $event)
    {
        foreach ($event->users as $user) {
            if ($event->task->creator_id !== $user->id) {
                $user->notify(new UserAssignedOnTask($event->task));
            }
        }
    }
}
