<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Task;
use App\TaskAssignation;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_is_notified_when_he_is_assigned_on_a_task()
    {
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create();
        $task->for($user);
        $this->assertCount(1, $user->notifications);
    }

    /** @test */
    public function user_is_not_notified_when_he_assignes_himself_on_a_task()
    {
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create(['creator_id' => $user->id]);
        $task->for($user);
        $this->assertCount(0, $user->notifications);
    }

    /** @test */
    public function only_newly_assigned_users_are_notified_when_added_new_assignees()
    {
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create();
        factory(TaskAssignation::class)->create([
            'user_id' => $user->id,
            'task_id' => $task->id
        ]);

        $newUser = factory(User::class)->create();
        $task->assign($newUser);
        $this->assertCount(0, $user->fresh()->notifications);
        $this->assertCount(1, $newUser->fresh()->notifications);
    }

    /** @test */
    public function user_is_notified_when_he_is_deassigned_from_a_task()
    {
        
        $task = factory(Task::class)->create();
        $user = factory(User::class)->create();
        factory(TaskAssignation::class)->create([
            'user_id' => $user->id,
            'task_id' => $task->id
        ]);
        $task->for([]);
        $this->assertCount(1, $user->fresh()->notifications);
    }

    /** @test */
    public function user_can_see_his_notifications()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $task = factory(Task::class)->create();
        $task->assign($user);

        $this->actingAs($user);
        $this->json('get', '/notifications')->assertJsonCount(1);
    }

    /** @test */
    public function notification_can_be_read()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(factory(User::class)->create());
        $notification = factory(DatabaseNotification::class)->create();
        $this->json('patch', "/notifications/{$notification->id}");
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function all_notifications_can_be_marked_as_read_at_onc()
    {
        $this->actingAs($user = factory(User::class)->create());
        $notifications = factory(DatabaseNotification::class, 2)->create([
            'notifiable_id' => $user->id
        ]);
        $this->assertCount(2, $user->unreadNotifications);
        $this->json('patch', "/notifications/read")
            ->assertStatus(200);
        $this->assertCount(0, $user->fresh()->unreadNotifications);
    }

    /** @test */
    public function user_is_notified_when_somebody_comment_on_task_he_is_also_assigned_to()
    {
        $task = factory(Task::class)->create();
        $users = factory(User::class, 2)->create();

        factory(TaskAssignation::class)->create([
            'task_id' => $task->id,
            'user_id' => $users[0]->id
        ]);

        factory(TaskAssignation::class)->create([
            'task_id' => $task->id,
            'user_id' => $users[1]->id
        ]);

        $this->assertCount(0, $users[0]->notifications);
        $this->assertCount(0, $users[1]->notifications);

        $this->actingAs($users[0]);

        $this->json('post', "/tasks/{$task->id}/comments", [
            'body' => 'Test'
        ]);

        $this->assertCount(0, $users[0]->fresh()->notifications);
        $this->assertCount(1, $users[1]->fresh()->notifications);
    }

    /** @test */
    public function notify_assigned_users_when_task_is_marked_as_finished()
    {
        $task = factory(Task::class)->create(['finished_at' => null]);
        $users = factory(User::class, 2)->create();

        factory(TaskAssignation::class)->create([
            'task_id' => $task->id,
            'user_id' => $users[0]->id
        ]);

        factory(TaskAssignation::class)->create([
            'task_id' => $task->id,
            'user_id' => $users[1]->id
        ]);

        $this->actingAs($users[0]);

        $this->json('post', "/finished-tasks/{$task->id}");

        $this->assertCount(0, $users[0]->fresh()->notifications);
        $this->assertCount(1, $users[1]->fresh()->notifications);
    }

    /** @test */
    public function notify_assigned_users_when_task_is_deleted()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create();
        factory(TaskAssignation::class)->create(['user_id' => $user->id, 'task_id' => $task->id]);
        $this->actingAs($task->creator);
        $this->json('delete', "/tasks/{$task->id}");
        $this->assertCount(1, $user->fresh()->notifications);
    }

    /** @test */
    public function notify_assigned_users_when_tasks_date_range_change()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $task = factory(Task::class)->create();
        factory(TaskAssignation::class)->create(['user_id' => $user->id, 'task_id' => $task->id]);

        $this->actingAs($task->creator);

        $this->json('patch', "/tasks/{$task->id}", [
            'start_date' => (new \Carbon\Carbon('+ 1 month'))->format('Y-m-d')
        ]);
        $this->assertCount(1, $user->notifications);
        $this->assertEquals("changed start date of", $user->notifications->first()->data['action']);

        $this->json('patch', "/tasks/{$task->id}", [
            'due_date' => (new \Carbon\Carbon('+ 1 month'))->format('Y-m-d')
        ]);

        $this->assertCount(2, $user->fresh()->notifications);
        $this->assertEquals("changed deadline of", $user->fresh()->notifications->last()->data['action']);
    }
}
