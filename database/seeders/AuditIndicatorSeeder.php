<?php

namespace Database\Seeders;

use App\Models\AuditFinding;
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

        $findingsDeleted = AuditFinding::query()->count();
        $indicatorsDeleted = AuditIndicator::query()->count();

        AuditFinding::query()->delete();
        AuditIndicator::query()->delete();

        $now = now();
        foreach (AuditIrregularityCatalog::all() as $row) {
            AuditIndicator::query()->create([
                'category' => $row['category'],
                'sub_category' => $row['sub_category'],
                'indicator_code' => $row['indicator_code'],
                'title' => $row['title'],
                'risk_rating' => $row['risk_rating'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command?->info(sprintf(
            'Findings Matrix catalog replaced: removed %d indicators / %d findings; seeded %d irregularity codes.',
            $indicatorsDeleted,
            $findingsDeleted,
            count(AuditIrregularityCatalog::all())
        ));
    }
}
