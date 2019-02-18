<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Comment;
use App\User;
use App\Task;

class CommentingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authorized_user_can_add_comment()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $task = factory(Task::class)->create();
        $task->assign($user);
        $this->assertCount(1, $user->tasks);
        $comment = factory(Comment::class)->raw();
        
        $this->json("post", "/tasks/{$task->id}/comments", $comment)
            ->assertStatus(201);
        
        $this->assertCount(1, $task->fresh()->comments);
        $this->assertEquals($user->id, $task->comments->first()->author->id);
    }

    /** @test */
    public function user_who_is_not_assigned_on_task_can_not_add_comment()
    {
        $task = factory(Task::class)->create();
        $this->actingAs(factory(User::class)->create());

        $this->json("post", "/tasks/{$task->id}/comments")
            ->assertStatus(403);
    }

    /** @test */
    public function comment_can_contain_attachments()
    {
        $this->withoutExceptionHandling();

        $task = factory(Task::class)->create();
        $task->assign($user = factory(User::class)->create());
        $this->actingAs($user);

        Storage::fake('public');

        $attachment = UploadedFile::fake()->create('idea.pdf');

        $this->json('post', "/tasks/{$task->id}/comments", [
            'body' => 'does not matter',
            'attachments' => [$attachment]
        ]);

        $this->assertCount(1, Storage::disk('public')->files('attachments'));
        $this->assertCount(1, $task->comments->first()->attachments);
    }

    /** @test */
    public function comment_requires_a_body()
    {
        $task = factory(Task::class)->create();
        $task->assign($user = factory(User::class)->create());
        $this->actingAs($user);
        $this->json('post', "/tasks/{$task->id}/comments", ['body' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }
}
