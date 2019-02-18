<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Task;

class UserAssignedOnTask extends Notification
{
    use Queueable;

    private $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'iniciator_avatar' => $this->task->creator->picture_path,
            'iniciator_name' => $this->task->creator->name,
            'action' => "assigned you on task",
            'task_name' => $this->task->title,
            'link' => url('tasks', [$this->task->id]),

            // 'message' => "{$this->task->creator->name} assigned you on <span class='notification__task-name'>{$this->task->title}</span>.",
            // 'link' => url('tasks', [$this->task->id]),
            // 'iniciator_avatar' => $this->task->creator->picture_path
        ];
    }
}
