<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Task;
use App\User;

abstract class UserAssignation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $users;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Task $task, $users)
    {
        $this->task = $task;
        $this->users = $this->parseUsers($users);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    protected function parseUsers($users)
    {
        if ($users instanceof User) {
            return [$users];

        }

        return $users;
    }
}
