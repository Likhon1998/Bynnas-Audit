<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Shakha;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Named “real-feeling” branches across divisions (English place + Shakha).
     * ShakhaSeeder pads the remainder up to 100.
     */
    public function run(): void
    {
        $catalog = [
            'Dhaka' => [
                'Dhaka North' => [
                    'Mirpur Shakha', 'Uttara Shakha', 'Gulshan Shakha', 'Banani Shakha',
                    'Mohakhali Shakha', 'Tejgaon Shakha', 'Pallabi Shakha', 'Kafrul Shakha',
                ],
                'Dhaka South' => [
                    'Dhanmondi Shakha', 'Motijheel Shakha', 'Lalbagh Shakha', 'Wari Shakha',
                    'Keraniganj Shakha', 'Jatrabari Shakha',
                ],
                'Gazipur' => [
                    'Gazipur Sadar Shakha', 'Tongi Shakha', 'Kaliakoir Shakha', 'Sreepur Shakha',
                ],
                'Narayanganj' => [
                    'Narayanganj Sadar Shakha', 'Fatullah Shakha', 'Sonargaon Shakha',
                ],
                'Tangail' => [
                    'Tangail Sadar Shakha', 'Mirzapur Shakha', 'Sakhipur Shakha',
                ],
                'Faridpur' => [
                    'Faridpur Sadar Shakha', 'Bhanga Shakha', 'Boalmari Shakha',
                ],
            ],
            'Chattogram' => [
                'Chattogram Metro' => [
                    'Agrabad Shakha', 'Halishahar Shakha', 'Pahartali Shakha', 'Chawkbazar Shakha',
                    'Patenga Shakha', 'Nasirabad Shakha',
                ],
                'Cox\'s Bazar' => [
                    'Cox\'s Bazar Sadar Shakha', 'Teknaf Shakha', 'Ukhiya Shakha',
                ],
                'Comilla' => [
                    'Comilla Sadar Shakha', 'Chandina Shakha', 'Laksam Shakha',
                ],
                'Noakhali' => [
                    'Noakhali Sadar Shakha', 'Begumganj Shakha', 'Companiganj Shakha',
                ],
                'Feni' => [
                    'Feni Sadar Shakha', 'Chhagalnaiya Shakha',
                ],
            ],
            'Rajshahi' => [
                'Rajshahi Sadar' => [
                    'Boalia Shakha', 'Motihar Shakha', 'Rajpara Shakha', 'Godagari Shakha',
                ],
                'Bogura' => [
                    'Bogura Sadar Shakha', 'Sherpur Bogura Shakha', 'Shibganj Shakha',
                ],
                'Pabna' => [
                    'Pabna Sadar Shakha', 'Ishwardi Shakha', 'Bera Shakha',
                ],
                'Sirajganj' => [
                    'Sirajganj Sadar Shakha', 'Ullapara Shakha', 'Shahjadpur Shakha',
                ],
            ],
            'Khulna' => [
                'Khulna Sadar' => [
                    'Sonadanga Shakha', 'Khalishpur Shakha', 'Daulatpur Shakha',
                ],
                'Jessore' => [
                    'Jessore Sadar Shakha', 'Benapole Shakha', 'Jhikargacha Shakha',
                ],
                'Kushtia' => [
                    'Kushtia Sadar Shakha', 'Bheramara Shakha', 'Kumarkhali Shakha',
                ],
                'Satkhira' => [
                    'Satkhira Sadar Shakha', 'Kaliganj Satkhira Shakha',
                ],
            ],
            'Barishal' => [
                'Barishal Sadar' => [
                    'Barishal Sadar Shakha', 'Bakerganj Shakha', 'Banaripara Shakha',
                ],
                'Patuakhali' => [
                    'Patuakhali Sadar Shakha', 'Kalapara Shakha',
                ],
                'Bhola' => [
                    'Bhola Sadar Shakha', 'Char Fasson Shakha',
                ],
            ],
            'Sylhet' => [
                'Sylhet Sadar' => [
                    'Sylhet Sadar Shakha', 'Zindabazar Shakha', 'Beanibazar Shakha',
                ],
                'Moulvibazar' => [
                    'Moulvibazar Sadar Shakha', 'Sreemangal Shakha',
                ],
                'Habiganj' => [
                    'Habiganj Sadar Shakha', 'Nabiganj Shakha',
                ],
            ],
            'Rangpur' => [
                'Rangpur Sadar' => [
                    'Rangpur Sadar Shakha', 'Gangachara Shakha', 'Mithapukur Shakha',
                ],
                'Dinajpur' => [
                    'Dinajpur Sadar Shakha', 'Birganj Shakha', 'Parbatipur Shakha',
                ],
                'Kurigram' => [
                    'Kurigram Sadar Shakha', 'Ulipur Shakha',
                ],
            ],
            'Mymensingh' => [
                'Mymensingh Sadar' => [
                    'Mymensingh Sadar Shakha', 'Trishal Shakha', 'Bhaluka Shakha',
                ],
                'Jamalpur' => [
                    'Jamalpur Sadar Shakha', 'Islampur Shakha',
                ],
                'Netrokona' => [
                    'Netrokona Sadar Shakha', 'Kendua Shakha',
                ],
            ],
        ];

        $codeSeq = 1;

        foreach ($catalog as $division => $areas) {
            foreach ($areas as $areaName => $shakhas) {
                $area = Area::query()->updateOrCreate(
                    ['name' => $areaName, 'division' => $division],
                    ['status' => 'active']
                );

                foreach ($shakhas as $shakhaName) {
                    Shakha::query()->updateOrCreate(
                        ['area_id' => $area->id, 'name' => $shakhaName],
                        [
                            'code' => 'BYN-'.str_pad((string) $codeSeq, 3, '0', STR_PAD_LEFT),
                            'status' => 'active',
                            'focal_person_name' => 'BM '.explode(' ', $shakhaName)[0],
                            'opening_date' => now()->subYears(rand(2, 12))->subDays(rand(0, 300))->toDateString(),
                        ]
                    );
                    $codeSeq++;
                }
            }
        }
    }
}
