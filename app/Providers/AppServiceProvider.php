<?php

namespace App\Providers;

use App\Support\AppTime;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
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
        // Whole product runs on Bangladesh time (UTC+6).
        AppTime::ensureConfigured();

        // Needed on older MySQL/MariaDB (cPanel) with utf8mb4 indexes.
        Schema::defaultStringLength(191);

        $this->syncDatabaseTimezone();

        Blade::directive('bdDateTime', function ($expression) {
            return "<?php echo bd_datetime($expression); ?>";
        });
        Blade::directive('bdDate', function ($expression) {
            return "<?php echo bd_date($expression); ?>";
        });
        Blade::directive('bdTime', function ($expression) {
            return "<?php echo bd_time($expression); ?>";
        });

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

    private function syncDatabaseTimezone(): void
    {
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        try {
            DB::statement("SET time_zone = '+06:00'");
        } catch (\Throwable) {
            // Host may disallow SET time_zone; app still formats via Asia/Dhaka.
        }
    }
}
