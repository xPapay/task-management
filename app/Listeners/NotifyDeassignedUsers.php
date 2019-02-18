<?php

namespace App\Listeners;

use App\Events\UsersDeassignedFromTask;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\UserDeassignedFromTask;

class NotifyDeassignedUsers
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
     * @param  UsersDeassignedFromTask  $event
     * @return void
     */
    public function handle(UsersDeassignedFromTask $event)
    {
        foreach ($event->users as $user) {
            if ($event->task->creator_id !== $user->id) {
                $user->notify(new UserDeassignedFromTask($event->task));
            }
        }
    }
}
