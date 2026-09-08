<?php

namespace Database\Seeders;

use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ShakhaEmployeeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $shakhas = Shakha::query()
            ->whereHas('auditReports', fn ($query) => $query->where('status', AuditReport::STATUS_COMPLETED))
            ->orderBy('id')
            ->get(['id', 'name', 'code']);

        if ($shakhas->isEmpty()) {
            $this->command?->error('No Shakha with a completed audit report was found.');

            return;
        }

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
        $colors = ['2563EB', 'DB2777', '059669', '7C3AED', 'EA580C', '0891B2', '4F46E5', 'BE123C', '15803D', 'A16207'];
        [$mailLocal, $mailDomain] = $this->mailboxParts();
        $createdOrUpdated = 0;

        foreach ($shakhas as $shakhaIndex => $shakha) {
            foreach ($firstNames as $employeeIndex => $firstName) {
                $number = $employeeIndex + 1;
                $name = $firstName.' '.$lastNames[($employeeIndex + $shakhaIndex) % count($lastNames)];
                $employeeCode = 'DEMO-'.str_pad((string) $shakha->id, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                $photoPath = 'shakha-employees/'.$shakha->id.'/demo-'.$number.'.svg';

                Storage::disk('public')->put(
                    $photoPath,
                    $this->avatarSvg($colors[$employeeIndex % count($colors)])
                );

                ShakhaEmployee::query()->updateOrCreate(
                    [
                        'shakha_id' => $shakha->id,
                        'employee_code' => $employeeCode,
                    ],
                    [
                        'name' => $name,
                        'designation' => $designations[$employeeIndex],
                        'phone' => '017'.str_pad((string) (($shakha->id * 1000000 + $number * 7919) % 100000000), 8, '0', STR_PAD_LEFT),
                        'email' => $mailLocal.'+shakha-'.$shakha->id.'-employee-'.$number.'@'.$mailDomain,
                        'joined_organization_at' => (2014 + (($employeeIndex + $shakhaIndex) % 8)).'-'.str_pad((string) (1 + ($employeeIndex % 9)), 2, '0', STR_PAD_LEFT).'-15',
                        'joined_shakha_at' => (2020 + (($employeeIndex + $shakhaIndex) % 5)).'-'.str_pad((string) (1 + ($employeeIndex % 8)), 2, '0', STR_PAD_LEFT).'-01',
                        'status' => 'active',
                        'sort_order' => $number,
                        'notes' => 'Demo employee for completed audit report communication.',
                        'photo_path' => $photoPath,
                    ]
                );
                $createdOrUpdated++;
            }

            $this->command?->info("Employees ready: {$shakha->name} ({$shakha->code}) — 10");
        }

        $this->command?->info("Done: {$createdOrUpdated} employees with email and profile picture across {$shakhas->count()} completed-report Shakhas.");
    }

    /**
     * Gmail plus-addresses all arrive in the configured sender inbox while remaining unique.
     *
     * @return array{0:string,1:string}
     */
    protected function mailboxParts(): array
    {
        $configured = strtolower(trim((string) config('mail.from.address')));
        if (filter_var($configured, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $configured, 2);

            return [preg_replace('/\+.*/', '', $local) ?: 'bynnasit', $domain];
        }

        return ['bynnasit', 'gmail.com'];
    }

    protected function avatarSvg(string $color): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320">
  <rect width="320" height="320" rx="32" fill="#{$color}"/>
  <circle cx="160" cy="116" r="58" fill="#ffffff" fill-opacity=".94"/>
  <path d="M55 292c6-72 47-112 105-112s99 40 105 112" fill="#ffffff" fill-opacity=".94"/>
  <circle cx="272" cy="48" r="20" fill="#ffffff" fill-opacity=".22"/>
</svg>
SVG;
    }
}
