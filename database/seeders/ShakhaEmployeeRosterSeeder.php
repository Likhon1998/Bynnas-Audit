<?php

namespace Database\Seeders;

use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use Illuminate\Database\Seeder;

/**
 * Idempotent production roster: 10 active কর্মী per shakha.
 * Safe to re-run — uses updateOrCreate on (shakha_id, employee_code).
 */
class ShakhaEmployeeRosterSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = [
            'রফিকুল', 'নাজমা', 'সোহেল', 'ফাতেমা', 'জাহিদ',
            'সালমা', 'করিম', 'রুমানা', 'ইমরান', 'শাহানা',
        ];
        $lastNames = ['ইসলাম', 'আক্তার', 'হোসেন', 'বেগম', 'রহমান'];
        $designations = [
            'শাখা ব্যবস্থাপক',
            'সহকারী শাখা ব্যবস্থাপক',
            'হিসাবরক্ষক',
            'ঋণ কর্মকর্তা',
            'সঞ্চয় কর্মকর্তা',
            'মাঠ কর্মকর্তা',
            'মাঠ কর্মকর্তা',
            'কমিউনিটি সংগঠক',
            'অফিস সহকারী',
            'সহায়ক কর্মী',
        ];

        $shakhas = Shakha::query()->orderBy('id')->get(['id', 'name', 'code']);
        if ($shakhas->isEmpty()) {
            $this->command?->warn('No shakhas found — skip employee roster seed.');

            return;
        }

        $touched = 0;
        foreach ($shakhas as $shakhaIndex => $shakha) {
            foreach ($firstNames as $employeeIndex => $firstName) {
                $number = $employeeIndex + 1;
                $name = $firstName.' '.$lastNames[($employeeIndex + $shakhaIndex) % count($lastNames)];
                $employeeCode = 'KRM-'.str_pad((string) $shakha->id, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);

                ShakhaEmployee::query()->updateOrCreate(
                    [
                        'shakha_id' => $shakha->id,
                        'employee_code' => $employeeCode,
                    ],
                    [
                        'name' => $name,
                        'designation' => $designations[$employeeIndex],
                        'phone' => '017'.str_pad((string) (($shakha->id * 1000000 + $number * 7919) % 100000000), 8, '0', STR_PAD_LEFT),
                        'email' => null,
                        'joined_organization_at' => (2014 + (($employeeIndex + $shakhaIndex) % 8)).'-'.str_pad((string) (1 + ($employeeIndex % 9)), 2, '0', STR_PAD_LEFT).'-15',
                        'joined_shakha_at' => (2020 + (($employeeIndex + $shakhaIndex) % 5)).'-'.str_pad((string) (1 + ($employeeIndex % 8)), 2, '0', STR_PAD_LEFT).'-01',
                        'status' => ShakhaEmployee::STATUS_ACTIVE,
                        'sort_order' => $number,
                        'notes' => null,
                    ]
                );
                $touched++;
            }
        }

        $this->command?->info("Shakha employee roster ready: {$touched} rows across {$shakhas->count()} shakhas (10 each).");
    }
}
