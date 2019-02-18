<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\UsersAssignedOnTask::class => [
            \App\Listeners\NotifyAssignedUsers::class
        ],
        \App\Events\UsersDeassignedFromTask::class => [
            \App\Listeners\NotifyDeassignedUsers::class
        ],
        \App\Events\CommentAdded::class => [
            \App\Listeners\NotifyAssignees::class
        ],
        \App\Events\TaskFinished::class => [
            \App\Listeners\NotifyAssignees::class
        ],
        \App\Events\TaskDeleted::class => [
            \App\Listeners\NotifyAssignees::class
        ],
        \App\Events\TaskStartChanged::class => [
            \App\Listeners\NotifyAssignees::class
        ],
        \App\Events\TaskEndChanged::class => [
            \App\Listeners\NotifyAssignees::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
