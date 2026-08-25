<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Shakha;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Dhaka' => [
                'Dhaka North' => ['Mirpur Shakha', 'Uttara Shakha', 'Gulshan Shakha'],
                'Dhaka South' => ['Dhanmondi Shakha', 'Motijheel Shakha'],
            ],
            'Chattogram' => [
                'Chattogram Metro' => ['Agrabad Shakha', 'Halishahar Shakha'],
            ],
            'Rajshahi' => [
                'Rajshahi Sadar' => ['Boalia Shakha'],
            ],
            'Khulna' => [
                'Khulna Sadar' => ['Sonadanga Shakha'],
            ],
        ];

        foreach ($catalog as $division => $areas) {
            foreach ($areas as $areaName => $shakhas) {
                $area = Area::query()->updateOrCreate(
                    ['name' => $areaName, 'division' => $division],
                    ['status' => 'active']
                );

                foreach ($shakhas as $index => $shakhaName) {
                    Shakha::query()->updateOrCreate(
                        ['area_id' => $area->id, 'name' => $shakhaName],
                        [
                            'code' => strtoupper(substr($division, 0, 3)).'-'.str_pad((string) ($area->id * 10 + $index + 1), 3, '0', STR_PAD_LEFT),
                            'status' => 'active',
                        ]
                    );
                }
            }
        }
    }
}
