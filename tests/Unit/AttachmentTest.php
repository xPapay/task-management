<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Attachment;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_attachment_has_a_path()
    {
        $attachment = factory(Attachment::class)->create([
            'path' => '/some/path'
        ]);

        $this->assertEquals('/some/path', $attachment->path);
    }

    /** @test */
    public function an_attachment_has_a_name()
    {
        $attachment = factory(Attachment::class)->create([
            'name' => 'filename.pdf'
        ]);

        $this->assertEquals('filename.pdf', $attachment->name);
    }
}
