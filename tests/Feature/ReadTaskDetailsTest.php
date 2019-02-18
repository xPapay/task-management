<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use App\User;

class ReadTaskDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function user_can_see_details_of_task_he_is_assigned_to()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $task = factory(Task::class)->create();
        $task->assign($this->user);
        $this->get("/tasks/{$task->id}")
            ->assertSee($task->title)
            ->assertSee($task->creator->name);
    }

    /** @test */
    public function user_can_see_who_is_assigned_to_the_task_with_him()
    {
        $this->withoutExceptionHandling();
        $task = factory(Task::class)->create();
        $anotherUser = factory(User::class)->create();
        $task->assign($this->user);
        $task->assign($anotherUser);
        $this->actingAs($this->user);
        $this->json('get', "/tasks/{$task->id}/assignees")
            ->assertSee($anotherUser->name)
            ->assertJsonCount(2);
    }

    /** @test */
    public function authenticated_assigned_user_can_see_tasks_comments()
    {   
        $this->withoutExceptionHandling();
        $task = factory(Task::class)->create();
        $comment = factory(\App\Comment::class)->create([
            'task_id' => $task->id
        ]);
        $task->assign($this->user);
        $this->actingAs($this->user);
        $this->json('get', "/tasks/{$task->id}/comments")
            ->assertStatus(200)
            ->assertSee($comment->body)
            ->assertJsonCount(1);
    }

    /** @test */
    public function unauthorized_user_can_not_see_tasks_comments()
    {
        $task = factory(Task::class)->create();
        $comment = factory(\App\Comment::class)->create([
            'task_id' => $task->id
        ]);
        $this->actingAs($this->user);
        $this->json('get', "/tasks/{$task->id}/comments")->assertStatus(403);
    }

    /** @test */
    public function aunathorized_user_can_not_see_details()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($this->user);
        $this->get("/tasks/{$task->id}")->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_can_is_redirected_to_login_in_order_to_see_details()
    {
        $task = factory(Task::class)->create();
        $this->get("/tasks/{$task->id}")->assertStatus(302);
    }
}
