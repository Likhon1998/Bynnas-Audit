<?php

namespace App\Console\Commands;

use App\Services\AuditorAccessSyncService;
use Illuminate\Console\Command;

class SyncAuditorAccessCommand extends Command
{
    protected $signature = 'access:sync-auditors';

    protected $description = 'Assign every auditor the correct role (audit_officer or auditor_reviewer if mapped)';

    public function handle(AuditorAccessSyncService $sync): int
    {
        $result = $sync->syncAll();

        $this->info('Package: '.implode(', ', $result['package']));
        $this->newLine();

        foreach ($result['users'] as $row) {
            $this->line("#{$row['id']} {$row['name']} <{$row['email']}>");
            $this->line('  '.implode(', ', $row['permissions']));
        }

        $this->newLine();
        $this->info("Synced {$result['synced']} auditor(s); {$result['with_review']} kept Act as reviewer.");

        return self::SUCCESS;
    }
}
