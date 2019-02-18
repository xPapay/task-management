<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Task;
use Carbon\Carbon;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function a_user_can_have_created_tasks()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->user->supervisedTasks);
    }

    /** @test */
    public function a_user_can_create_a_task()
    {
        $task = factory(Task::class)->make();
        $task = $this->user->createTask($task->toArray());

        $this->assertCount(1, $this->user->supervisedTasks);
        $this->assertInstanceOf(Task::class, $task);
    }

    /** @test */
    public function user_can_create_a_task_with_assignees()
    {
        $task = factory(Task::class)->raw();
        $assignees = factory(User::class, 2)->create();
        $task = $this->user->createTask($task)->for($assignees);

        $this->assertCount(1, $assignees[0]->tasks);
        $this->assertDatabaseHas('task_user', ['user_id' => $assignees[0]->id, 'task_id' => $task->id]);
    }

    /** @test */
    public function user_can_have_tasks()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->user->tasks);
    }
}
