<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use App\User;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_task_has_a_creator()
    {
        $task = factory(Task::class)->create();
        $this->assertInstanceOf('App\User', $task->creator);
    }

    /** @test */
    public function a_task_can_have_assignees()
    {
        $task = factory(Task::class)->create();
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $task->assignees);    
    }

    /** @test */
    public function a_task_can_be_assigned_to_user()
    {
        $task = factory(Task::class)->create();
        $user = factory(User::class)->create();
        $task->assign($user);
        $this->assertCount(1, $task->assignees);
    }

    /** @test */
    public function a_task_can_be_assigned_to_multiple_users_at_once()
    {
        $task = factory(Task::class)->create();
        $users = factory(User::class, 2)->create();
        $task->assign($users);
        $this->assertCount(2, $task->assignees);
    }

    /** @test */
    public function task_can_be_finished()
    {
        $task = factory(Task::class)->create();
        $task->finish();
        $this->assertNotNull($task->fresh()->finished_at);        
    }

    /** @test */
    public function task_can_be_unfinished()
    {
        $task = factory(Task::class)->create(['finished_at' => \Carbon\Carbon::now()]);
        $this->assertNotNull($task->finished_at);
        $task->unfinish();
        $this->assertNull($task->fresh()->finished_at);
    }

    /** @test */
    public function task_can_tell_who_marked_it_as_finished()
    {
        $task = factory(Task::class)->create(['finisher_id' => factory(User::class)->create()->id]);
        $this->assertInstanceOf('\App\User', $task->finisher);
    }

    /** @test */
    public function task_can_have_comments()
    {
        $task = factory(Task::class)->create();
        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Collection', $task->comments);
    }

    /** @test */
    public function task_can_have_attachments()
    {
        $task = factory(Task::class)->create();
        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Collection', $task->attachments);
    }
}
