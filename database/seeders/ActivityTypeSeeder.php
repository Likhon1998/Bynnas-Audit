<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\AuditPolicy;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Audit', 'slug' => 'audit', 'category_map' => AuditPolicy::CATEGORY_SHAKHA, 'sort_order' => 1],
            ['name' => 'Monitoring', 'slug' => 'monitoring', 'category_map' => AuditPolicy::CATEGORY_PROJECT_MONITORING, 'sort_order' => 2],
            ['name' => 'Inspection', 'slug' => 'inspection', 'category_map' => null, 'sort_order' => 3],
            ['name' => 'Joint Format', 'slug' => 'joint-format', 'category_map' => null, 'sort_order' => 4],
            ['name' => 'Training', 'slug' => 'training', 'category_map' => null, 'sort_order' => 5],
            ['name' => 'Special Audit', 'slug' => 'special-audit', 'category_map' => null, 'sort_order' => 6],
            ['name' => 'Special Monitoring', 'slug' => 'special-monitoring', 'category_map' => null, 'sort_order' => 7],
            ['name' => 'Other', 'slug' => 'other', 'category_map' => null, 'sort_order' => 8],
            ['name' => 'Area Office Audit', 'slug' => 'area-office', 'category_map' => AuditPolicy::CATEGORY_AREA, 'sort_order' => 9],
            ['name' => 'PKSF / Maternity', 'slug' => 'pksf-maternity', 'category_map' => AuditPolicy::CATEGORY_PKSF, 'sort_order' => 10],
            ['name' => 'HQ Concern', 'slug' => 'hq-concern', 'category_map' => AuditPolicy::CATEGORY_HQ, 'sort_order' => 11],
            ['name' => 'Project Audit', 'slug' => 'project-audit', 'category_map' => AuditPolicy::CATEGORY_PROJECT_AUDIT, 'sort_order' => 12],
        ];

        foreach ($types as $type) {
            ActivityType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                $type + ['is_active' => true]
            );
        }
    }
}
