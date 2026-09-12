<?php

namespace Database\Seeders;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\ShakhaEmployeeTransfer;
use App\Models\User;
use App\Services\AuditSummaryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Logical authority-proof demo:
 * Same কর্মী (fixed ID) named in 3 financial findings across 3 shakhas / years,
 * with transfers between them — so the dossier shows "৩ বার".
 *
 * Safe to re-run: keyed by employee_code DEMO-REPEAT-001.
 */
class RepeatOffenderDemoSeeder extends Seeder
{
    public const EMPLOYEE_CODE = 'DEMO-REPEAT-001';

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->first()
            ?? User::query()->orderBy('id')->first();

        $shakhaA = Shakha::query()->where('code', 'BYN-001')->orWhere('name', 'like', 'Mirpur%')->orderBy('id')->first();
        $shakhaB = Shakha::query()->where('code', 'BYN-002')->orWhere('name', 'like', 'Uttara%')->orderBy('id')->first();
        $shakhaC = Shakha::query()->where('code', 'BYN-003')->orWhere('name', 'like', 'Gulshan%')->orderBy('id')->first();

        if (! $shakhaA || ! $shakhaB || ! $shakhaC || ! $admin) {
            $this->command?->warn('RepeatOffenderDemoSeeder: need 3 shakhas + a user. Skipped.');

            return;
        }

        $indPassbookCut = AuditIndicator::query()->where('indicator_code', '১০০০-২')->first()
            ?? AuditIndicator::query()->where('is_active', true)->orderBy('id')->skip(1)->first();
        $indNoEntry = AuditIndicator::query()->where('indicator_code', '১০০০-১')->first()
            ?? AuditIndicator::query()->where('is_active', true)->orderBy('id')->first();
        $indUnauthorizedLoan = AuditIndicator::query()->where('indicator_code', '২০০০-২')->first()
            ?? AuditIndicator::query()->where('is_active', true)->orderBy('id')->skip(2)->first();

        if (! $indPassbookCut || ! $indNoEntry || ! $indUnauthorizedLoan) {
            $this->command?->warn('RepeatOffenderDemoSeeder: indicators missing. Skipped.');

            return;
        }

        DB::transaction(function () use (
            $admin,
            $shakhaA,
            $shakhaB,
            $shakhaC,
            $indPassbookCut,
            $indNoEntry,
            $indUnauthorizedLoan
        ) {
            $employee = ShakhaEmployee::query()->updateOrCreate(
                ['employee_code' => self::EMPLOYEE_CODE],
                [
                    'shakha_id' => $shakhaA->id,
                    'name' => 'Demo Kormi Alpha',
                    'designation' => 'Field Officer (Demo)',
                    'phone' => null,
                    'joined_organization_at' => '2019-03-01',
                    'joined_shakha_at' => '2019-03-01',
                    'status' => ShakhaEmployee::STATUS_ACTIVE,
                    'sort_order' => 1,
                    'notes' => 'FICTIONAL demo identity for UI proof only (fixed ID '.self::EMPLOYEE_CODE.'). Not a real person.',
                ]
            );

            // Clear prior demo trail for idempotent re-run.
            ShakhaEmployeeTransfer::query()->where('shakha_employee_id', $employee->id)->delete();
            AuditFinding::query()
                ->whereNotNull('responsible_staff_ids')
                ->get()
                ->each(function (AuditFinding $finding) use ($employee) {
                    if (in_array((int) $employee->id, $finding->responsibleStaffIds(), true)) {
                        $finding->delete();
                    }
                });
            AuditReport::query()
                ->where('user_id', $admin->id)
                ->whereIn('shakha_id', [$shakhaA->id, $shakhaB->id, $shakhaC->id])
                ->get()
                ->each(function (AuditReport $report) {
                    $pages = is_array($report->pages_data) ? $report->pages_data : [];
                    if (($pages['demo_repeat_offender'] ?? false) === true) {
                        $report->delete();
                    }
                });

            $summary = app(AuditSummaryService::class);
            $staffLabel = $employee->name.' ('.$employee->employee_code.')';

            // --- Occurrence 1: Mirpur, Jan 2024 — passbook balance cut / misappropriation ---
            $employee->update([
                'shakha_id' => $shakhaA->id,
                'joined_shakha_at' => '2019-03-01',
                'status' => ShakhaEmployee::STATUS_ACTIVE,
            ]);
            $this->seedVisit(
                $summary,
                $admin,
                $employee,
                $shakhaA,
                $indPassbookCut,
                1,
                2024,
                48500.00,
                12,
                3,
                'সদস্য পাসবুকে এন্ট্রি কাটিয়া ৳৪৮,৫০০ অসামঞ্জস্য পাওয়া যায় (ডেমো কেস)। দায়িত্বপ্রাপ্ত: Demo Kormi Alpha।',
                $staffLabel,
                'Demo occurrence 1 — Mirpur Jan 2024'
            );

            // Transfer A → B (May 2024) after first finding
            $this->transfer($employee, $shakhaA, $shakhaB, '2024-05-15', 'Demo transfer after Mirpur review', $admin->id);

            // --- Occurrence 2: Uttara, Jun 2025 — installment collected without passbook entry ---
            $this->seedVisit(
                $summary,
                $admin,
                $employee,
                $shakhaB,
                $indNoEntry,
                6,
                2025,
                31200.00,
                20,
                5,
                'পাসবইতে এন্ট্রি না দিয়ে কিস্তি আদায়ের নমুনায় ঘাটতি ৳৩১,২০০ (ডেমো কেস)।',
                $staffLabel,
                'Demo occurrence 2 — Uttara Jun 2025'
            );

            // Transfer B → C (Feb 2026)
            $this->transfer($employee, $shakhaB, $shakhaC, '2026-02-10', 'Demo transfer to Gulshan; prior findings remain on fixed ID', $admin->id);

            // --- Occurrence 3: Gulshan, Sep 2026 — loan disbursed without approval ---
            $this->seedVisit(
                $summary,
                $admin,
                $employee,
                $shakhaC,
                $indUnauthorizedLoan,
                9,
                2026,
                125000.00,
                8,
                2,
                'ঋণ অনুমোদন ব্যতীত ঋণ বিতরণের নমুনা; মোট ৳১,২৫,০০০ (ডেমো কেস)।',
                $staffLabel,
                'Demo occurrence 3 — Gulshan Sep 2026'
            );
        });

        $this->command?->info(
            'Repeat offender demo ready: '.self::EMPLOYEE_CODE
            .' — open /shakha-employees and search, or Findings Summary Sep 2026.'
        );
    }

    private function transfer(
        ShakhaEmployee $employee,
        Shakha $from,
        Shakha $to,
        string $joinedAt,
        string $note,
        int $byUserId
    ): void {
        ShakhaEmployeeTransfer::query()->create([
            'shakha_employee_id' => $employee->id,
            'from_shakha_id' => $from->id,
            'to_shakha_id' => $to->id,
            'transferred_at' => $joinedAt.' 10:00:00',
            'note' => $note,
            'transferred_by' => $byUserId,
        ]);

        $line = 'Transferred from '.$from->name.' → '.$to->name.' on '.$joinedAt.' — '.$note;
        $notes = trim((string) ($employee->notes ?? ''));
        $employee->update([
            'shakha_id' => $to->id,
            'joined_shakha_at' => $joinedAt,
            'status' => ShakhaEmployee::STATUS_ACTIVE,
            'notes' => $notes !== '' ? $notes."\n".$line : $line,
        ]);
    }

    private function seedVisit(
        AuditSummaryService $summary,
        User $admin,
        ShakhaEmployee $employee,
        Shakha $shakha,
        AuditIndicator $indicator,
        int $month,
        int $year,
        float $amount,
        int $sample,
        int $irregs,
        string $observation,
        string $staffLabel,
        string $findingTitle
    ): void {
        $report = AuditReport::query()->create([
            'user_id' => $admin->id,
            'shakha_id' => $shakha->id,
            'report_month' => $month,
            'report_year' => $year,
            'status' => AuditReport::STATUS_COMPLETED,
            'completed_at' => sprintf('%04d-%02d-28 16:00:00', $year, $month),
            'progress_pct' => 100,
            'pages_data' => [
                'demo_repeat_offender' => true,
                'page4' => [
                    'reportBlocks' => [
                        [
                            'type' => 'finding',
                            'indicator_id' => $indicator->id,
                            'body' => $findingTitle,
                            'amount' => (string) $amount,
                        ],
                        [
                            'type' => 'observation',
                            'label' => 'পর্যবেক্ষণ (Observation) :',
                            'body' => $observation,
                            'matrix_people' => [[
                                'id' => $employee->id,
                                'code' => $employee->employee_code,
                                'name' => $employee->name,
                            ]],
                            'show_matrix_people' => true,
                        ],
                        [
                            'type' => 'stats',
                            'linked_indicator_id' => $indicator->id,
                            'rows' => [[
                                'sample_size' => (string) $sample,
                                'instances_found' => (string) $irregs,
                            ]],
                        ],
                    ],
                ],
            ],
        ]);

        $summary->syncFromReport($report);

        // Ensure IDs + display label even if sync path edge-cases.
        $finding = AuditFinding::query()
            ->where('shakha_id', $shakha->id)
            ->where('audit_indicator_id', $indicator->id)
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->first();

        if ($finding) {
            $finding->update([
                'responsible_staff_name' => $staffLabel,
                'responsible_staff_ids' => [(int) $employee->id],
                'observation' => $observation,
                'amount' => $amount,
                'sample_size_checked' => $sample,
                'irregularity_count' => $irregs,
            ]);
        }
    }
}
