<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\OctoberDemoReportsSeeder;
use Illuminate\Console\Command;

class SeedOctoberDemoReportsCommand extends Command
{
    protected $signature = 'demo:october-reports
        {--owner= : Email or ID of the user who owns the 3 reports (default: October visit officer / first audit officer)}
        {--share=* : Email or ID of another user who should also see the reports (repeatable)}';

    protected $description = 'Create/refresh the 3 October 2026 demo audit reports (never touches real reports)';

    public function handle(): int
    {
        $owner = null;
        if (filled($this->option('owner'))) {
            $owner = $this->findUser((string) $this->option('owner'));
            if (! $owner) {
                $this->error('No user found for --owner='.$this->option('owner'));

                return self::FAILURE;
            }
        }

        $shareIds = [];
        foreach ((array) $this->option('share') as $needle) {
            $user = $this->findUser((string) $needle);
            if (! $user) {
                $this->error('No user found for --share='.$needle);

                return self::FAILURE;
            }
            $shareIds[] = (int) $user->id;
        }

        OctoberDemoReportsSeeder::$ownerOverride = $owner;
        OctoberDemoReportsSeeder::$shareWith = $shareIds;

        try {
            return $this->call('db:seed', ['--class' => OctoberDemoReportsSeeder::class, '--force' => true]);
        } finally {
            OctoberDemoReportsSeeder::$ownerOverride = null;
            OctoberDemoReportsSeeder::$shareWith = [];
        }
    }

    private function findUser(string $needle): ?User
    {
        $needle = trim($needle);
        if ($needle === '') {
            return null;
        }

        return ctype_digit($needle)
            ? User::query()->find((int) $needle)
            : User::query()->where('email', $needle)->first();
    }
}
