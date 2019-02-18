<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use App\User;

class FinishMyTasksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_who_is_assigned_on_task_can_finish_it()
    {
        $this->withoutExceptionHandling();
        $task = factory(Task::class)->create(['finished_at' => null]);
        $this->actingAs($user = factory(User::class)->create());
        $task->assign($user);
        $this->post("/finished-tasks/{$task->id}")
            ->assertStatus(200)
            ->assertSee('finished_at');
        $this->assertNotNull($task->fresh()->finished_at);
        $this->assertEquals($user->id, $task->fresh()->finisher->id);
    }

    /** @test */
    public function a_user_who_is_assigned_on_task_can_unfinish_finished_tasks()
    {
        $this->withoutExceptionHandling();
        $task = factory(Task::class)->create(['finished_at' => new \DateTime()]);
        $this->actingAs($user = factory(User::class)->create());
        $task->assign($user);
        $this->delete("/finished-tasks/{$task->id}")
            ->assertStatus(200)
            ->assertSee('finished_at');
        $this->assertNull($task->fresh()->finished_at);
    }

    /** @test */
    public function unauthorized_user_can_not_finish_task()
    {
        $task = factory(Task::class)->create();
        $this->actingAs(factory(User::class)->create());
        $this->json('post', "/finished-tasks/{$task->id}")
            ->assertStatus(403);
        $this->assertNull($task->fresh()->finished_at);
    }

    /** @test */
    public function unauthorized_user_can_not_unfinish_task()
    {
        $task = factory(Task::class)->create(['finished_at' => new \DateTime()]);
        $this->actingAs(factory(User::class)->create());
        $this->json('delete', "/finished-tasks/{$task->id}")
            ->assertStatus(403);
        $this->assertNotNull($task->fresh()->finished_at);
    }
}
