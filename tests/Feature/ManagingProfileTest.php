<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\User;
use Auth;

class ManagingProfileTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function user_can_upload_new_profile_picture()
    {
        Storage::fake('public');

        $this->actingAs($this->user);

        $avatar = UploadedFile::fake()->create('avatar.jpg', 100, 100);

        $response = $this->json('post', '/profile', [
            'picture' => $avatar
        ]);
        
        Storage::disk('public')->assertExists("profiles/{$avatar->hashName()}");
        
        $this->assertEquals("profiles/{$avatar->hashName()}", Auth::user()->fresh()->picture);
        $response->assertStatus(200)
            ->assertJsonFragment(['picture' => Auth::user()->fresh()->picture]);
    }

    /** @test */
    public function user_can_change_his_name()
    {
        $this->withoutExceptionHandling();
        
        $this->actingAs($this->user);

        $this->json('patch', '/profile', [
            'name' => 'Updated Name'
        ])->assertStatus(200);

        $this->assertEquals('Updated Name', $this->user->fresh()->name);
    }
}
