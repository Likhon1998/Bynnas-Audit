<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_is_available_at_the_login_path(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Welcome to your audit workspace');
    }
}
