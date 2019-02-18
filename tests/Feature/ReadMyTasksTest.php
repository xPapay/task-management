<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Task;
use Illuminate\Auth\AuthenticationException;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use App\Attachment;
use Illuminate\Support\Facades\Storage;

class ReadMyTasksTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function authenticated_user_can_see_tasks_he_is_assigned_to()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $task = factory(Task::class)->create();
        $task->assign($this->user);
        $this->json('get', '/tasks')->assertSee($task->title);
    }

    /** @test */
    public function user_can_not_see_tasks_he_is_not_assigned_to()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $task = factory(Task::class)->create();
        $this->json('get', '/tasks')->assertDontSee($task->title);
    }

    /** @test */
    public function unathenticated_user_is_not_allowed_to_browse_any_tasks()
    {
        $this->withoutExceptionHandling();
        $this->expectException(AuthenticationException::class);
        $this->get('/tasks');
    }

    /** @test */
    public function user_can_filter_out_unfinished_tasks()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $unfinishedTask = factory(Task::class)->create(['finished_at' => null]);
        $finishedTask = factory(Task::class)->create(['created_at' => new Carbon('3 days ago')]);
        $unfinishedTask->assign($this->user);
        $finishedTask->assign($this->user);
        $finishedTask->finish();
        
        $this->json('get', '/tasks?status=unfinished')
            ->assertSee($unfinishedTask->title)
            ->assertDontSee($finishedTask->title);
    }

    /** @test */
    public function user_can_filter_out_finished_tasks()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $unfinishedTask = factory(Task::class)->create(['finished_at' => null]);
        $finishedTask = factory(Task::class)->create(['created_at' => new Carbon('3 days ago')]);
        $unfinishedTask->assign($this->user);
        $finishedTask->assign($this->user);
        $finishedTask->finish();
        
        $this->json('get', '/tasks?status=finished')
            ->assertSee($finishedTask->title)
            ->assertDontSee($unfinishedTask->title);
    }

    /** @test */
    public function user_can_filter_out_tasks_lying_between_dates()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);

        $filters = [
            'sinceDate' => new \Carbon\Carbon('-1 week'),
            'untilDate' => new \Carbon\Carbon('+1 week')
        ];

        $tasks = [
            factory(Task::class)->create([
                'start_date' => $filters['sinceDate']->copy()->subDays(2),
                'due_date' => $filters['sinceDate']->copy()->subDay()
            ]),
            factory(Task::class)->create([
                'start_date' => $filters['sinceDate']->copy()->subDay(),
                'due_date' => $filters['sinceDate']->copy()->addDay()
            ]),
            factory(Task::class)->create([
                'start_date' => $filters['sinceDate']->copy()->addDay(),
                'due_date' => $filters['untilDate']->copy()->subDay()
            ]),
            factory(Task::class)->create([
                'start_date' => $filters['untilDate']->copy()->subDay(),
                'due_date' => $filters['untilDate']->copy()->addDays(2)
            ]),
            factory(Task::class)->create([
                'start_date' => $filters['untilDate']->copy()->addDay(),
                'due_date' => $filters['untilDate']->copy()->addDays(2)
            ])
        ];

        $tasks[0]->assign($this->user);
        $tasks[1]->assign($this->user);
        $tasks[2]->assign($this->user);
        $tasks[3]->assign($this->user);
        $tasks[4]->assign($this->user);

        $sinceDate = $filters['sinceDate']->toDateString();
        $untilDate = $filters['untilDate']->toDateString();

        $this->json('get', "/tasks?sinceDate={$sinceDate}&untilDate={$untilDate}")
            ->assertJsonCount(3)
            ->assertSee($tasks[1]->title)
            ->assertSee($tasks[2]->title)
            ->assertSee($tasks[3]->title)
            ->assertDontSee($tasks[0]->title)
            ->assertDontSee($tasks[4]->title);
    }

    /** @test */
    public function user_can_download_attached_files()
    {
        $this->withoutExceptionHandling();
        $task = factory(Task::class)->create();
        $file = UploadedFile::fake()->create('file.jpg');
        Storage::fake('public');
        $attachment = Attachment::fromFile($file);
        $task->attachments()->save($attachment);
        $task->assign($this->user);
        $this->actingAs($this->user);
        $this->get("/attachments/{$attachment->id}")
            ->assertStatus(200);
    }
}
