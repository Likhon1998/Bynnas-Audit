<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class AuditIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $path = storage_path('app/templates/audit/AUDIT_FINDINGS_CONSOLIDATED_FORMAT.xlsx');
        if (! is_file($path)) {
            $this->command?->warn('Excel catalog missing at '.$path);

            return;
        }

        Artisan::call('audit:import-indicators', [
            'filepath' => $path,
            '--fresh' => true,
            '--sheet' => 'August, 2026',
        ]);

        $this->command?->getOutput()?->write(Artisan::output());
    }
}
