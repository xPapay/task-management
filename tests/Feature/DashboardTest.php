<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\AuthenticationException;

class DashboardTest extends TestCase
{
    /** @test */
    public function unauthenticated_user_can_not_access_dashboard()
    {
        $this->withoutExceptionHandling();
        $this->expectException(AuthenticationException::class);
        $this->get('/');
    }
}
