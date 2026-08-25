<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Shakha;
use App\Support\Divisions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShakhaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = $this->ensureAreas();
        Shakha::query()->update(['status' => 'active']);
        $target = 376;

        $existingCodes = Shakha::query()->pluck('code')->filter()->all();
        $nextNumber = 1;
        $created = Shakha::query()->count();

        if ($created >= $target) {
            return;
        }

        $rows = [];
        $usedNames = Shakha::query()
            ->get(['area_id', 'name'])
            ->map(fn ($row) => $row->area_id.'|'.$row->name)
            ->all();

        $areaList = $areas->values();
        $areaCount = $areaList->count();
        $now = now();
        $perAreaCount = [];

        while ($created + count($rows) < $target) {
            $index = $created + count($rows);
            $area = $areaList[$index % $areaCount];
            $areaId = $area->id;
            $perAreaCount[$areaId] = ($perAreaCount[$areaId] ?? 0) + 1;

            do {
                $code = 'SHA-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
                $nextNumber++;
            } while (in_array($code, $existingCodes, true));

            $name = $area->name.' Branch '.$perAreaCount[$areaId];
            $key = $areaId.'|'.$name;

            if (in_array($key, $usedNames, true)) {
                continue;
            }

            $existingCodes[] = $code;
            $usedNames[] = $key;

            $rows[] = [
                'area_id' => $areaId,
                'name' => $name,
                'code' => $code,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('shakhas')->insert($chunk);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Area>
     */
    private function ensureAreas()
    {
        $catalog = [
            'Dhaka' => ['Dhaka North', 'Dhaka South', 'Gazipur', 'Narayanganj', 'Tangail', 'Kishoreganj', 'Faridpur', 'Madaripur'],
            'Chattogram' => ['Chattogram Metro', 'Cox\'s Bazar', 'Comilla', 'Noakhali', 'Feni', 'Rangamati'],
            'Rajshahi' => ['Rajshahi Sadar', 'Bogura', 'Pabna', 'Natore', 'Sirajganj'],
            'Khulna' => ['Khulna Sadar', 'Jessore', 'Kushtia', 'Satkhira'],
            'Barishal' => ['Barishal Sadar', 'Patuakhali', 'Bhola'],
            'Sylhet' => ['Sylhet Sadar', 'Moulvibazar', 'Habiganj'],
            'Rangpur' => ['Rangpur Sadar', 'Dinajpur', 'Kurigram'],
            'Mymensingh' => ['Mymensingh Sadar', 'Jamalpur', 'Netrokona'],
        ];

        foreach ($catalog as $division => $areaNames) {
            if (! in_array($division, Divisions::OPTIONS, true)) {
                continue;
            }

            foreach ($areaNames as $areaName) {
                Area::query()->updateOrCreate(
                    ['name' => $areaName, 'division' => $division],
                    ['status' => 'active']
                );
            }
        }

        return Area::query()->orderBy('id')->get();
    }
}
