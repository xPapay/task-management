<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskNotification extends Notification
{
    use Queueable;

    private $data;
    private $requiredFields = ['iniciator_avatar', 'iniciator_name', 'action', 'task_name', 'link'];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($this->requiredFields as $field) {
            if (! array_key_exists($field, $data)) {
                throw new \Exception("{$field} does not exists on given data");
            }
        }

        $this->data = $data;
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
        return $this->data;
        // [
        //     'iniciator_avatar' => $this->comment->author->picture_path,
        //     'iniciator_name' => $this->comment->author->name,
        //     'action' => "commented on",
        //     'task_name' => $this->comment->task->title,
        //     'link' => url('tasks', [$this->comment->task->id]),
        // ];
    }
}
