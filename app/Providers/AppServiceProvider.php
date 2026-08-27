<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Needed on older MySQL/MariaDB (cPanel) with utf8mb4 indexes.
        Schema::defaultStringLength(191);

        // Superadmin bypass: Spatie role OR legacy is_superadmin flag.
        Gate::before(function ($user, $ability) {
            if (! is_object($user)) {
                return null;
            }

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            if (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
                return true;
            }

            return null;
        });
    }
}
