<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('Bynnas Audit')
            ->assertSee('Welcome to your audit workspace')
            ->assertDontSee('Continue with Google')
            ->assertDontSee('Google');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_credentials_can_authenticate(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'admin@bynnasaudit.com',
            'password' => '12345678',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_logout_with_stale_csrf_still_lands_on_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->withMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->post('/logout', ['_token' => 'stale-invalid-token']);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status');
    }

    public function test_token_mismatch_for_authenticated_user_does_not_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = \Illuminate\Http\Request::create(
            '/profile',
            'PATCH',
            ['name' => $user->name, 'email' => $user->email],
            server: ['HTTP_REFERER' => route('dashboard')]
        );
        $request->setLaravelSession($this->app['session']->driver());
        $request->setUserResolver(fn () => $user);
        $this->app['session']->setPreviousUrl(route('dashboard'));

        // Same shape Laravel uses after preparing TokenMismatchException.
        $response = $this->app[\Illuminate\Contracts\Debug\ExceptionHandler::class]
            ->render(
                $request,
                new \Symfony\Component\HttpKernel\Exception\HttpException(419, 'CSRF token mismatch.')
            );

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('dashboard'), $response->headers->get('Location'));
        $this->assertSame(
            'Your form expired. Please try again — you are still signed in.',
            $this->app['session']->get('status')
        );
    }

    public function test_csrf_token_endpoint_returns_token(): void
    {
        $response = $this->getJson(route('csrf.token'));

        $response->assertOk()->assertJsonStructure(['token']);
        $this->assertNotEmpty($response->json('token'));
    }
}
