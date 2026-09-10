<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Rebuild the database to match the local demo dataset.
 * WARNING: wipes all application data.
 */
class InstallDemoDataCommand extends Command
{
    protected $signature = 'demo:install
                            {--force : Required in production; confirms wipe}';

    protected $description = 'Wipe DB and install the same demo data used locally (organogram, shakhas, visits, matrix, reports, logins)';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Production detected. Re-run with --force if you really want to WIPE live data and install demo.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will DELETE all data and reinstall local-style demo. Continue?')) {
            $this->warn('Cancelled.');

            return self::FAILURE;
        }

        $this->warn('Installing demo dataset (migrate:fresh --seed)…');

        $exit = Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ], $this->output);

        if ($exit !== 0) {
            $this->error('Demo install failed.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Demo install complete — live now matches local demo.');
        $this->line('Admin: admin@bynnasaudit.com / '.\App\Support\RoleAccess::DEFAULT_PASSWORD);
        $this->line('Staff: organogram employee email / '.\App\Support\RoleAccess::DEFAULT_PASSWORD);

        return self::SUCCESS;
    }
}
