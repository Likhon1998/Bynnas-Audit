<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

class OrganogramSeeder extends Seeder
{
    public function run(): void
    {
        $legacySlugs = ['senior-director-audit', 'senior-office-audit'];
        $target = Position::query()->where('slug', 'senior-officer-audit')->first();

        foreach ($legacySlugs as $legacySlug) {
            $legacy = Position::query()->where('slug', $legacySlug)->first();

            if (! $legacy) {
                continue;
            }

            if ($target && $target->id !== $legacy->id) {
                Employee::query()
                    ->where('position_id', $legacy->id)
                    ->update(['position_id' => $target->id]);
                $legacy->delete();
            } else {
                $legacy->update([
                    'title' => 'Senior Officer Audit',
                    'slug' => 'senior-officer-audit',
                ]);
                $target = $legacy;
            }
        }

        $ranks = [
            ['serial' => 1, 'title' => 'Director Audit', 'slug' => 'director-audit', 'color' => '#2563EB'],
            ['serial' => 2, 'title' => 'Joint Director Audit', 'slug' => 'joint-director-audit', 'color' => '#7A5AF8'],
            ['serial' => 3, 'title' => 'Deputy Director Audit', 'slug' => 'deputy-director-audit', 'color' => '#0BA5EC'],
            ['serial' => 4, 'title' => 'Assistant Director Audit', 'slug' => 'assistant-director-audit', 'color' => '#12B76A'],
            ['serial' => 5, 'title' => 'Senior Officer Audit', 'slug' => 'senior-officer-audit', 'color' => '#F79009'],
            ['serial' => 6, 'title' => 'Officer Audit', 'slug' => 'officer-audit', 'color' => '#EE46BC'],
            ['serial' => 7, 'title' => 'Audit Officer', 'slug' => 'audit-officer', 'color' => '#667085'],
        ];

        $people = [
            'director-audit' => [
                ['name' => 'Mahmud Hasan', 'email' => 'mahmud.hasan@bynnasaudit.com'],
                ['name' => 'Farhana Rahman', 'email' => 'farhana.rahman@bynnasaudit.com'],
            ],
            'joint-director-audit' => [
                ['name' => 'Imran Chowdhury', 'email' => 'imran.chowdhury@bynnasaudit.com'],
                ['name' => 'Nusrat Jahan', 'email' => 'nusrat.jahan@bynnasaudit.com'],
            ],
            'deputy-director-audit' => [
                ['name' => 'Rafiqul Islam', 'email' => 'rafiqul.islam@bynnasaudit.com'],
                ['name' => 'Shamima Akter', 'email' => 'shamima.akter@bynnasaudit.com'],
                ['name' => 'Tanvir Ahmed', 'email' => 'tanvir.ahmed@bynnasaudit.com'],
            ],
            'assistant-director-audit' => [
                ['name' => 'Ayesha Siddique', 'email' => 'ayesha.siddique@bynnasaudit.com'],
                ['name' => 'Kamal Hossain', 'email' => 'kamal.hossain@bynnasaudit.com'],
                ['name' => 'Lamia Karim', 'email' => 'lamia.karim@bynnasaudit.com'],
            ],
            'senior-officer-audit' => [
                ['name' => 'Shahidul Alam', 'email' => 'shahidul.alam@bynnasaudit.com'],
                ['name' => 'Rina Sultana', 'email' => 'rina.sultana@bynnasaudit.com'],
            ],
            'officer-audit' => [
                ['name' => 'Arif Khan', 'email' => 'arif.khan@bynnasaudit.com'],
                ['name' => 'Mitu Das', 'email' => 'mitu.das@bynnasaudit.com'],
                ['name' => 'Jahidul Islam', 'email' => 'jahidul.islam@bynnasaudit.com'],
            ],
            'audit-officer' => [
                ['name' => 'Sajid Mahmud', 'email' => 'sajid.mahmud@bynnasaudit.com'],
                ['name' => 'Nabila Haque', 'email' => 'nabila.haque@bynnasaudit.com'],
                ['name' => 'Omar Faruk', 'email' => 'omar.faruk@bynnasaudit.com'],
                ['name' => 'Tania Rahman', 'email' => 'tania.rahman@bynnasaudit.com'],
            ],
        ];

        foreach ($ranks as $rank) {
            $position = Position::query()->where('slug', $rank['slug'])->first()
                ?? Position::query()->where('serial', $rank['serial'])->first();

            if ($position) {
                $position->update($rank);
            } else {
                $position = Position::query()->create($rank);
            }

            foreach ($people[$rank['slug']] as $index => $person) {
                Employee::query()->updateOrCreate(
                    ['email' => $person['email']],
                    [
                        'position_id' => $position->id,
                        'name' => $person['name'],
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
