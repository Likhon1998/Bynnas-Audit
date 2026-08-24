<?php

namespace App\Providers;

use App\Models\Employee;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.sidebar', function ($view) {
            $memberCount = 0;

            if (Schema::hasTable('employees')) {
                $memberCount = Employee::query()->count();
            }

            $view->with('sidebarMemberCount', $memberCount);
        });
    }
}
