<?php

namespace Database\Seeders;

use App\Models\AuditIndicator;
use App\Support\AuditIrregularityCatalog;
use Illuminate\Database\Seeder;

class AuditIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $codes = [];
        $now = now();

        foreach (AuditIrregularityCatalog::all() as $row) {
            $code = (string) $row['indicator_code'];
            $codes[] = $code;

            AuditIndicator::query()->updateOrCreate(
                ['indicator_code' => $code],
                [
                    'category' => $row['category'],
                    'sub_category' => $row['sub_category'],
                    'title' => $row['title'],
                    'risk_rating' => $row['risk_rating'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]
            );
        }

        // Soft-retire old Excel-era codes instead of wiping findings.
        $retired = AuditIndicator::query()
            ->whereNotIn('indicator_code', $codes)
            ->update(['is_active' => false, 'updated_at' => $now]);

        $this->command?->info(sprintf(
            'Findings Matrix catalog synced: %d active irregularity codes%s.',
            count($codes),
            $retired > 0 ? ", {$retired} legacy indicator(s) deactivated" : ''
        ));
    }
}
