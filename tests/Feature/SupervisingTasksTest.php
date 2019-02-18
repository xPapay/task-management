<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Attachment;
use App\Comment;
use App\User;
use App\Task;

class SupervisingTasksTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function an_authenticated_user_can_create_a_task()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $task = factory(Task::class)->make();
        $assignees = factory(User::class, 2)->create();
        $data = array_merge($task->toArray(), ['assignees' => [$assignees[0]->id, $assignees[1]->id]]);
        $this->json('post', '/tasks', $data)
            ->assertSee($task->title)
            ->assertSee($assignees[0]->id);

        $this->assertCount(1, $this->user->supervisedTasks);
        $this->assertCount(1, $assignees[0]->tasks);
    }

    /** @test */
    public function unathenticated_user_can_not_create_a_task()
    {
        $this->withoutExceptionHandling();
        $this->expectException(AuthenticationException::class);
        $this->post('/tasks', []);
    }

    /** @test */
    public function user_can_see_which_tasks_is_he_supervising()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $task = factory(Task::class)->make();
        $this->user->createTask($task->toArray());
        $task2 = factory(Task::class)->create();
        
        $this->json("get", "/tasks/supervising")
            ->assertSee($task->title)
            ->assertDontSee($task2->title);
    }

    /** @test */
    public function user_can_edit_task_he_is_supervising()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        $task = $this->user->createTask(factory(Task::class)->raw());
        $updatedTask = factory(Task::class)->raw();
        $this->json("patch", "/tasks/{$task->id}", $updatedTask)
            ->assertStatus(200);
        
        $this->json('get', '/tasks/supervising')
            ->assertSee($updatedTask['title'])
            ->assertDontSee($task->title);
    }

    /** @test */
    public function user_can_not_edit_task_he_is_not_supervising()
    {
        $this->actingAs($this->user);
        $task = factory(Task::class)->create();

        $this->json("patch", "/tasks/{$task->id}", [])->assertStatus(403);
    }

    /** @test */
    public function task_requires_a_title()
    {
        $this->actingAs($this->user);
        $this->json("post", "/tasks", factory(Task::class)->raw(['title' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /** @test */
    public function task_requires_start_date()
    {
        $this->actingAs($this->user);
        $this->json("post", "/tasks", factory(Task::class)->raw(['start_date' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('start_date');
    }

    /** @test */
    public function task_requires_due_date()
    {
        $this->actingAs($this->user);
        $this->json("post", "/tasks", factory(Task::class)->raw(['due_date' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('due_date');
    }

    /** @test */
    public function start_date_must_be_less_than_due_date()
    {
        $this->actingAs($this->user);
        $this->json("post", "/tasks", factory(Task::class)->raw([
            'start_date' => new \Carbon\Carbon(),
            'due_date' => new \Carbon\Carbon('-1 day')
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date', 'start_date']);
    }

    /** @test */
    public function task_must_have_at_least_one_assignee()
    {
        $this->actingAs($this->user);
        $res = $this->json("post", "/tasks", factory(Task::class)->raw())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['assignees']);
    }

    /** @test */
    public function user_can_upload_attachments()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');
        $fakeAttachment = UploadedFile::fake()->create('inventory.pdf');

        $this->actingAs($this->user);

        $task = factory(Task::class)->raw([
            'assignees' => [$this->user->id],
            'attachments' => [
                $fakeAttachment
            ]
        ]);
        
        $this->json("post", "/tasks", $task)
            ->assertStatus(201);

        $this->assertEquals("attachments/{$fakeAttachment->hashName()}", Task::where('title', $task['title'])->first()->attachments[0]->path);
        
        Storage::disk('public')->assertExists("attachments/{$fakeAttachment->hashName()}");
    }

    /** @test */
    public function user_can_remove_attachments()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        Storage::fake('public');
        
        $task = factory(Task::class)->create(["creator_id" => $this->user->id]);
        
        $attachments = [
            Attachment::fromFile(UploadedFile::fake()->create('file1.pdf')),
            Attachment::fromFile(UploadedFile::fake()->create('file2.pdf'))
        ];

        $task->attachments()->save($attachments[0]);
        $task->attachments()->save($attachments[1]);

        Storage::disk('public')->assertExists("{$attachments[0]['path']}");
        Storage::disk('public')->assertExists("{$attachments[1]['path']}");
        $this->assertCount(2, $task->attachments);

        $this->json("patch", "/tasks/{$task->id}", [
            'old_attachments' => [1]
        ])->assertStatus(200);

        $this->assertCount(1, $task->fresh()->attachments);
        $this->assertEquals($attachments[0]['path'], $task->fresh()->attachments->first()->path);
        Storage::disk('public')->assertMissing("{$attachments[1]['path']}");
    }

    /** @test */
    public function user_can_delete_task_he_created()
    {
        $this->withoutExceptionHandling();

        Storage::fake('public');

        $task = factory(Task::class)->create(['creator_id' => $this->user->id]);
        $comment = factory(Comment::class)->create(['task_id' => $task->id]);

        $attachment = Attachment::fromFile(UploadedFile::fake()->create('file1.pdf'));

        $task->attachments()->save($attachment);

        $this->actingAs($this->user);

        $this->delete("/tasks/{$task->id}")
            ->assertRedirect('/');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('attachments', ['path' => $attachment[0]['path']]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        Storage::disk('public')->assertMissing("{$attachment[0]['path']}");
    }

    /** @test */
    public function unauthenticated_user_is_not_allowed_to_delete_task()
    {
        $task = factory(Task::class)->create();
        $this->json('delete', "/tasks/{$task->id}")
            ->assertStatus(401);
        $this->assertDatabaseHas('tasks', ['title' => $task->title]);
    }

    /** @test */
    public function user_can_not_delete_task_he_did_not_create()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($this->user);
        $this->json('delete', "/tasks/{$task->id}")
            ->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['title' => $task->title]);
    }

    /** @test */
    public function user_can_query_users_by_name()
    {
        $this->withoutExceptionHandling();

        $jamesSmith = factory(User::class)->create(['name' => 'James Smith']);
        $this->actingAs($jamesSmith);

        $johnDoe = factory(User::class)->create(['name' => 'John Doe']);
        $johnMitch = factory(User::class)->create(['name' => 'John Mitch']);

        $this->json('get', '/users?nameLike=mit')
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => $jamesSmith->name])
            ->assertJsonFragment(['name' => $johnMitch->name])
            ->assertJsonMissing(['name' => $johnDoe->name]);
    }

    /** @test */
    public function validate_title_only_when_present_on_update()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $this->json('patch', "/tasks/{$task->id}", ['title' => null])
            ->assertStatus(422)->assertJsonValidationErrors('title');
        $this->assertNotNull($task->fresh()->title);

        $this->json('patch', "/tasks/{$task->id}", [])
            ->assertStatus(200);
    }

    /** @test */
    public function validate_start_date_only_when_present_on_update()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $this->json('patch', "/tasks/{$task->id}", ['start_date' => null])
            ->assertStatus(422)->assertJsonValidationErrors('start_date');
        $this->assertNotNull($task->fresh()->start_date);

        $this->json('patch', "/tasks/{$task->id}", [])
            ->assertStatus(200);
    }

    /** @test */
    public function validate_due_date_only_when_present_on_update()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $this->json('patch', "/tasks/{$task->id}", ['due_date' => null])
            ->assertStatus(422)->assertJsonValidationErrors('due_date');
        $this->assertNotNull($task->fresh()->due_date);

        $this->json('patch', "/tasks/{$task->id}", [])
            ->assertStatus(200);
    }

    /** @test */
    public function validate_assignees_only_when_present_on_update()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $this->json('patch', "/tasks/{$task->id}", ['assignees' => []])
            ->assertStatus(422)->assertJsonValidationErrors('assignees');
        $this->assertNotNull($task->fresh()->assignees);

        $this->json('patch', "/tasks/{$task->id}", [])
            ->assertStatus(200);
    }

    /** @test */
    public function new_due_date_must_be_greater_than_start_date()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $weekBeforeStart = $task->start_date->copy()->subWeek();
        $this->json('patch', "/tasks/{$task->id}", ['due_date' => $weekBeforeStart->toDateString()])
            ->assertStatus(422)->assertJsonValidationErrors('due_date');        
    }

    /** @test */
    public function new_start_date_must_be_less_than_due_date()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $weekAfterDueDate = $task->due_date->copy()->addWeek();
        $this->json('patch', "/tasks/{$task->id}", ['start_date' => $weekAfterDueDate->toDateString()])
            ->assertStatus(422)->assertJsonValidationErrors('start_date');
    }

    /** @test */
    public function new_due_date_must_be_greater_than_new_start_date()
    {
        $task = factory(Task::class)->create();
        $this->actingAs($task->creator);
        $this->json('patch', "/tasks/{$task->id}", [
            'start_date' => '2019-02-02', 
            'due_date' => '2019-01-01'
        ])->assertStatus(422)->assertJsonValidationErrors('due_date');
    }
}
