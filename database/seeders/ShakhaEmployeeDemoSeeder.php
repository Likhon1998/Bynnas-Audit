<?php

namespace Database\Seeders;

use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use Illuminate\Database\Seeder;

class ShakhaEmployeeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $shakha = Shakha::query()->orderBy('id')->first();
        if (! $shakha) {
            $this->command?->error('No shakha found.');

            return;
        }

        $people = [
            ['DHA-011-001', 'মোঃ রফিকুল ইসলাম', 'শাখা ব্যবস্থাপক'],
            ['DHA-011-002', 'নাজমা আক্তার', 'সহকারী শাখা ব্যবস্থাপক'],
            ['DHA-011-003', 'সোহেল রানা', 'মাঠকর্মী'],
            ['DHA-011-004', 'ফাতেমা বেগম', 'মাঠকর্মী'],
            ['DHA-011-005', 'জাহিদ হাসান', 'মাঠকর্মী'],
            ['DHA-011-006', 'সালমা খাতুন', 'হিসাবরক্ষক'],
            ['DHA-011-007', 'করিম উদ্দিন', 'অফিস সহকারী'],
            ['DHA-011-008', 'রুমানা আফরোজ', 'মাঠকর্মী'],
            ['DHA-011-009', 'ইমরান হোসেন', 'মাঠকর্মী'],
            ['DHA-011-010', 'শাহানা পারভীন', 'কেন্দ্র ব্যবস্থাপক'],
        ];

        foreach ($people as $i => [$code, $name, $designation]) {
            ShakhaEmployee::query()->updateOrCreate(
                [
                    'shakha_id' => $shakha->id,
                    'employee_code' => $code,
                ],
                [
                    'name' => $name,
                    'designation' => $designation,
                    'phone' => '0171'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT),
                    'joined_organization_at' => '201'.(5 + ($i % 4)).'-0'.(1 + ($i % 8)).'-15',
                    'joined_shakha_at' => '202'.(1 + ($i % 4)).'-0'.(1 + ($i % 6)).'-01',
                    'status' => 'active',
                    'sort_order' => $i + 1,
                    'notes' => 'Demo staff for Findings Matrix Staff autocomplete',
                ]
            );
        }

        $this->command?->info("Seeded 10 employees for {$shakha->name} (id {$shakha->id}).");
    }
}
