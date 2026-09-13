<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // cPanel / reverse-proxy HTTPS
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'superadmin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel converts TokenMismatchException → HttpException(419) before render callbacks.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $isLogout = $request->routeIs('logout') || $request->is('logout');

            // Logout with a stale token should still clear the session quietly.
            if ($isLogout) {
                try {
                    Auth::guard('web')->logout();
                } catch (\Throwable) {
                    //
                }

                if ($request->hasSession()) {
                    try {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    } catch (\Throwable) {
                        //
                    }
                }

                return redirect()
                    ->route('login')
                    ->with('status', 'You have been logged out.');
            }

            // Stale CSRF on a still-valid login is common on long-lived pages.
            // Do NOT force logout — refresh the token and let the user retry.
            $stillLoggedIn = Auth::check();

            if ($request->hasSession()) {
                try {
                    $request->session()->regenerateToken();
                } catch (\Throwable) {
                    //
                }
            }

            $freshToken = $request->hasSession() ? csrf_token() : null;

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $stillLoggedIn
                        ? 'Your form expired. Please try again.'
                        : 'Your session expired. Please sign in again.',
                    'token' => $freshToken,
                ], 419);
            }

            if (! $stillLoggedIn) {
                return redirect()
                    ->route('login')
                    ->with('status', 'Your session expired. Please sign in again.');
            }

            return redirect()
                ->back(fallback: route('dashboard'))
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('status', 'Your form expired. Please try again — you are still signed in.');
        });
    })->create();
