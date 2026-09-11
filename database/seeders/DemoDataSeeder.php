<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\AuditReport;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\PlanSchedule;
use App\Models\Shakha;
use App\Models\User;
use App\Models\VisitExecution;
use App\Support\AuditIrregularityCatalog;
use App\Support\FinancialYear;
use App\Support\RoleAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Demo / showcase data across modules. Safe to re-run (skips when marker findings exist).
 * Clean later with: php artisan migrate:fresh --seed  (or wipe demo tables manually).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (AuditFinding::query()->where('observation', 'like', '[DEMO]%')->exists()) {
            $this->command?->info('DemoDataSeeder: already seeded, skipping.');

            return;
        }

        $this->ensureDemoIndicators();
        $this->seedDemoUsers();
        $this->seedMonthlyAndAnnualKpis();
        $this->seedRiskAssessments();
        $this->seedFindings();
        $this->seedMonthlyVisits();
        $this->seedAuditReports();

        $this->command?->info('DemoDataSeeder: demo data loaded across shakhas, KPIs, risk, findings, visits, reports.');
    }

    protected function ensureDemoIndicators(): void
    {
        if (AuditIndicator::query()->exists()) {
            return;
        }

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
    }

    protected function seedDemoUsers(): void
    {
        $managerEmployee = Employee::query()->where('email', 'imran.chowdhury@bynnasaudit.com')->first();
        $officerEmployees = Employee::query()
            ->whereIn('email', [
                'arif.khan@bynnasaudit.com',
                'mitu.das@bynnasaudit.com',
                'sajid.mahmud@bynnasaudit.com',
                'nabila.haque@bynnasaudit.com',
            ])
            ->get();

        // Prefer organogram login (same email as employee). Demo aliases stay only if unused.
        if ($managerEmployee) {
            $manager = User::query()->where('employee_id', $managerEmployee->id)->first()
                ?? User::query()->updateOrCreate(
                    ['email' => 'manager@bynnasaudit.com'],
                    [
                        'name' => $managerEmployee->name,
                        'password' => Hash::make(RoleAccess::DEFAULT_PASSWORD),
                        'email_verified_at' => now(),
                        'is_superadmin' => false,
                        'is_active' => true,
                        'employee_id' => $managerEmployee->id,
                    ]
                );
            if (method_exists($manager, 'syncRoles') && ! $manager->isSuperAdmin()) {
                $manager->syncRoles(['audit_manager']);
            }
        }

        $shakhas = Shakha::query()->orderBy('id')->limit(80)->pluck('id');
        $chunks = $shakhas->chunk(20)->values();

        foreach ($officerEmployees->values() as $index => $employee) {
            $user = User::query()->where('employee_id', $employee->id)->first();
            if (! $user) {
                $user = User::query()->updateOrCreate(
                    ['email' => 'officer'.($index + 1).'@bynnasaudit.com'],
                    [
                        'name' => $employee->name,
                        'password' => Hash::make(RoleAccess::DEFAULT_PASSWORD),
                        'email_verified_at' => now(),
                        'is_superadmin' => false,
                        'is_active' => true,
                        'employee_id' => $employee->id,
                    ]
                );
            }
            if (method_exists($user, 'syncRoles') && ! $user->isSuperAdmin()) {
                $user->syncRoles(['audit_officer']);
            }

            $ids = $chunks->get($index)?->all() ?? [];
            if ($ids !== []) {
                $user->assignedShakhas()->syncWithoutDetaching($ids);
            }
        }
    }

    protected function seedMonthlyAndAnnualKpis(): void
    {
        if (! Schema::hasTable('shakha_monthly_kpis') && ! Schema::hasTable('shakha_annual_kpis')) {
            return;
        }

        $shakhas = Shakha::query()->orderBy('id')->limit(120)->get();
        $fyLabel = AuditPlan::query()->value('fy_label') ?: '2026-2027';
        $now = now();
        $monthlyRows = [];
        $annualRows = [];

        foreach ($shakhas as $i => $shakha) {
            $baseMembers = 800 + ($i * 17) % 2200;
            $borrowers = (int) round($baseMembers * 0.62);
            $odBorrowers = (int) round($borrowers * (0.04 + ($i % 7) * 0.01));

            if (Schema::hasTable('shakha_monthly_kpis')) {
                foreach ([6, 7, 8] as $month) {
                    $year = 2026;
                    $monthlyRows[] = [
                        'shakha_id' => $shakha->id,
                        'report_month' => $month,
                        'report_year' => $year,
                        'total_samities' => 40 + ($i % 30),
                        'total_members' => $baseMembers + $month * 3,
                        'total_borrowers' => $borrowers,
                        'total_od_borrowers' => $odBorrowers,
                        'monthly_members_admitted' => 8 + ($i % 12),
                        'monthly_members_dropout' => 2 + ($i % 5),
                        'field_officer_count' => 4 + ($i % 4),
                        'savings_balance' => 450000 + $i * 8500,
                        'loan_outstanding' => 1800000 + $i * 22000,
                        'total_od_taka' => 25000 + $odBorrowers * 1200,
                        'monthly_savings_collection' => 85000 + $i * 400,
                        'monthly_savings_withdrawal' => 22000 + $i * 150,
                        'monthly_disbursement_amount' => 320000 + $i * 2100,
                        'monthly_loan_recovery' => 280000 + $i * 1800,
                        'monthly_current_recovery' => 240000 + $i * 1500,
                        'monthly_recoverable' => 300000 + $i * 1600,
                        'due_loanee_loan_outstanding' => 90000 + $i * 900,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (Schema::hasTable('shakha_annual_kpis')) {
                $annualRows[] = [
                    'shakha_id' => $shakha->id,
                    'fy_label' => $fyLabel,
                    'fo_count' => 4 + ($i % 4),
                    'total_samities' => 40 + ($i % 30),
                    'total_members' => $baseMembers,
                    'total_borrowers' => $borrowers,
                    'total_od_borrowers' => $odBorrowers,
                    'fy_members_admission' => 90 + ($i % 40),
                    'fy_members_dropout' => 25 + ($i % 15),
                    'fy_disbursement_borrowers' => (int) round($borrowers * 0.35),
                    'fy_fully_repayment_borrowers' => (int) round($borrowers * 0.18),
                    'fy_savings_collection' => 900000 + $i * 5000,
                    'fy_savings_withdrawal' => 250000 + $i * 1200,
                    'savings_balance' => 450000 + $i * 8500,
                    'fy_disbursement_amount' => 3500000 + $i * 25000,
                    'fy_loan_recovery' => 3100000 + $i * 20000,
                    'loan_outstanding' => 1800000 + $i * 22000,
                    'recoverable' => 3200000 + $i * 18000,
                    'current_recovery' => 2800000 + $i * 16000,
                    'due_recovery' => 180000 + $i * 2000,
                    'total_od_taka' => 25000 + $odBorrowers * 1200,
                    'due_loanee_loan_outstanding' => 90000 + $i * 900,
                    'own_fund_until_prior_june' => 120000 + $i * 1100,
                    'surplus_deficit_fy' => ($i % 2 === 0 ? 1 : -1) * (15000 + $i * 200),
                    'new_due' => 12000 + $i * 100,
                    'due_increase_this_month' => 3000 + ($i % 10) * 200,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($monthlyRows !== []) {
            foreach (array_chunk($monthlyRows, 200) as $chunk) {
                DB::table('shakha_monthly_kpis')->insertOrIgnore($chunk);
            }
        }
        if ($annualRows !== []) {
            foreach (array_chunk($annualRows, 100) as $chunk) {
                DB::table('shakha_annual_kpis')->insertOrIgnore($chunk);
            }
        }
    }

    protected function seedRiskAssessments(): void
    {
        if (! Schema::hasTable('shakha_risk_assessments')) {
            return;
        }
        $shakhas = Shakha::query()->orderBy('id')->limit(100)->get();
        $now = now();
        $rows = [];
        $categories = ['Low Risk', 'Medium Risk', 'High Risk', 'Significant Risk'];

        foreach ($shakhas as $i => $shakha) {
            $score = 25 + ($i * 3) % 70;
            $category = $categories[min(3, intdiv($score, 25))];
            $rows[] = [
                'shakha_id' => $shakha->id,
                'assessment_month' => 7,
                'assessment_year' => 2026,
                'distance_from_area_office_km' => 5 + ($i % 40),
                'total_income' => 500000 + $i * 8000,
                'total_expenditure' => 420000 + $i * 7000,
                'write_off_principal_amount' => ($i % 5 === 0) ? 15000 + $i * 100 : 0,
                'savings_adjustment_amount' => ($i % 7 === 0) ? 5000 : 0,
                'overdue_principal_31_365_days' => 20000 + ($i % 20) * 1500,
                'has_both_bm_and_abm' => $i % 3 !== 0,
                'special_audit_last_two_years' => $i % 11 === 0,
                'total_weighted_score' => $score,
                'risk_category' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('shakha_risk_assessments')->insertOrIgnore($chunk);
        }
    }

    protected function seedFindings(): void
    {
        $indicators = AuditIndicator::query()->orderBy('id')->limit(12)->get();
        if ($indicators->isEmpty()) {
            return;
        }

        $shakhas = Shakha::query()->orderBy('id')->limit(60)->get();
        $now = now();
        $rows = [];
        $staff = ['করিম উদ্দিন', 'সালমা আক্তার', 'রফিকুল ইসলাম', 'নাজমা বেগম', 'জাহিদ হাসান'];

        foreach ($shakhas as $si => $shakha) {
            foreach ($indicators->take(4 + ($si % 4)) as $ii => $indicator) {
                $hasIssue = ($si + $ii) % 3 !== 0;
                $rows[] = [
                    'shakha_id' => $shakha->id,
                    'audit_indicator_id' => $indicator->id,
                    'audit_month' => 7 + ($si % 2),
                    'audit_year' => 2026,
                    'amount' => $hasIssue ? round(5000 + ($si * 317 + $ii * 89) % 85000, 2) : null,
                    'sample_size_checked' => 10 + ($si % 20),
                    'irregularity_count' => $hasIssue ? 1 + ($ii % 4) : 0,
                    'observation' => $hasIssue
                        ? '[DEMO] '.$indicator->title.' — নমুনা পরীক্ষায় অসামঞ্জস্য পাওয়া গেছে।'
                        : '[DEMO] যাচাইকৃত; উল্লেখযোগ্য অনিয়ম নেই।',
                    'responsible_staff_name' => $staff[($si + $ii) % count($staff)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 150) as $chunk) {
            DB::table('audit_findings')->insertOrIgnore($chunk);
        }
    }

    protected function seedMonthlyVisits(): void
    {
        $plan = AuditPlan::query()->orderByDesc('id')->first();
        $activity = ActivityType::query()->where('slug', 'audit')->first()
            ?? ActivityType::query()->first();
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->first();
        $employees = Employee::query()->orderBy('id')->limit(12)->get();

        if (! $plan || ! $activity || $employees->isEmpty()) {
            return;
        }

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', AuditPolicy::CATEGORY_SHAKHA)
            ->where('schedulable_type', Shakha::class)
            ->whereIn('month_index', [0, 1, 2])
            ->orderBy('id')
            ->limit(90)
            ->get();

        if ($schedules->isEmpty()) {
            // Fallback: invent work items for first 40 shakhas without plan schedules
            $schedules = Shakha::query()->orderBy('id')->limit(40)->get()->map(function (Shakha $shakha, int $i) {
                return (object) [
                    'id' => null,
                    'month_index' => $i % 3,
                    'schedulable_type' => Shakha::class,
                    'schedulable_id' => $shakha->id,
                    'category' => AuditPolicy::CATEGORY_SHAKHA,
                    'entity' => $shakha,
                ];
            });
        }

        /** @var array<int, list<array{0:string,1:string}>> $busyByEmployee */
        $busyByEmployee = [];
        foreach ($employees as $emp) {
            $busyByEmployee[(int) $emp->id] = [];
        }

        // Respect already-seeded bookings so re-runs stay conflict-free.
        MonthlyAssignment::query()
            ->where(function ($q) use ($employees) {
                $ids = $employees->pluck('id')->all();
                $q->whereIn('employee_id', $ids)
                    ->orWhereHas('visitors', fn ($v) => $v->whereIn('employees.id', $ids));
            })
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->with('visitors')
            ->get()
            ->each(function (MonthlyAssignment $assignment) use (&$busyByEmployee) {
                $range = [$assignment->start_date->toDateString(), $assignment->end_date->toDateString()];
                foreach ($assignment->visitorList() as $visitor) {
                    $eid = (int) $visitor->id;
                    if (! isset($busyByEmployee[$eid])) {
                        $busyByEmployee[$eid] = [];
                    }
                    $busyByEmployee[$eid][] = $range;
                }
            });

        foreach ($schedules as $index => $schedule) {
            $shakhaId = $schedule->schedulable_id;
            $shakha = $schedule->entity ?? Shakha::query()->find($shakhaId);
            if (! $shakha) {
                continue;
            }

            $monthIndex = (int) $schedule->month_index;
            $workItem = MonthlyWorkItem::query()->firstOrCreate(
                [
                    'audit_plan_id' => $plan->id,
                    'month_index' => $monthIndex,
                    'category' => AuditPolicy::CATEGORY_SHAKHA,
                    'schedulable_type' => Shakha::class,
                    'schedulable_id' => $shakha->id,
                    'source' => MonthlyWorkItem::SOURCE_YEARLY,
                ],
                [
                    'fy_label' => $plan->fy_label,
                    'activity_type_id' => $activity->id,
                    'plan_schedule_id' => $schedule->id ?? null,
                    'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
                    'entity_label' => $shakha->name,
                    'notes' => '[DEMO] Auto-assigned monthly visit',
                    'created_by' => $admin?->id,
                ]
            );

            if ($workItem->assignment()->exists()) {
                continue;
            }

            $monthStart = FinancialYear::fromLabel($plan->fy_label)->dateForMonthIndex($monthIndex);
            $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
            $duration = 1 + ($index % 3); // 1–3 calendar days

            $placement = $this->findConflictFreeDemoSlot(
                $employees,
                $busyByEmployee,
                $monthStart,
                $monthEnd,
                $duration,
                $index
            );

            if ($placement === null) {
                // Leave unassigned rather than create an illegal overlap.
                continue;
            }

            [$employee, $start, $end] = $placement;
            $busyByEmployee[(int) $employee->id][] = [$start->toDateString(), $end->toDateString()];

            $assignment = MonthlyAssignment::query()->create([
                'monthly_work_item_id' => $workItem->id,
                'employee_id' => $employee->id,
                'visit_date' => $start->toDateString(),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'duration_days' => $start->diffInDays($end) + 1,
                'duration_mode' => 'calendar',
                'count_off_days' => false,
                'purpose' => 'শাখা নিরীক্ষা (ডেমো)',
                'remarks' => '[DEMO] Sample visit assignment',
                'assigned_by' => $admin?->id,
            ]);

            $assignment->visitors()->sync([
                $employee->id => ['sort_order' => 1],
            ]);

            $statusPool = [
                VisitExecution::STATUS_PLANNED,
                VisitExecution::STATUS_IN_PROGRESS,
                VisitExecution::STATUS_COMPLETED,
                VisitExecution::STATUS_DELAYED,
            ];
            $status = $statusPool[$index % count($statusPool)];

            VisitExecution::query()->create([
                'monthly_assignment_id' => $assignment->id,
                'status' => $status,
                'actual_start_date' => in_array($status, [VisitExecution::STATUS_IN_PROGRESS, VisitExecution::STATUS_COMPLETED], true)
                    ? $start->toDateString()
                    : null,
                'actual_end_date' => $status === VisitExecution::STATUS_COMPLETED
                    ? $end->toDateString()
                    : null,
                'actual_duration_days' => $status === VisitExecution::STATUS_COMPLETED
                    ? $start->diffInDays($end) + 1
                    : null,
                'actual_employee_id' => $employee->id,
                'remarks' => '[DEMO] Visit execution sample',
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ]);

            $workItem->update(['status' => MonthlyWorkItem::STATUS_ASSIGNED]);
        }

        // Safety net: clear any illegal same-person overlaps left from older demo runs.
        $worklist = app(\App\Services\MonthlyWorklistService::class);
        foreach ([0, 1, 2] as $monthIndex) {
            $worklist->resolveOverlappingAllocations($plan, $monthIndex, $admin?->id);
        }
    }

    /**
     * Pick employee + dates with no overlap against already-booked demo ranges.
     *
     * @param  \Illuminate\Support\Collection<int, Employee>  $employees
     * @param  array<int, list<array{0:string,1:string}>>  $busyByEmployee
     * @return array{0:Employee,1:\Carbon\Carbon,2:\Carbon\Carbon}|null
     */
    protected function findConflictFreeDemoSlot(
        $employees,
        array $busyByEmployee,
        \Carbon\Carbon $monthStart,
        \Carbon\Carbon $monthEnd,
        int $durationDays,
        int $seedIndex,
    ): ?array {
        $count = $employees->count();
        if ($count === 0 || $durationDays < 1) {
            return null;
        }

        $overlaps = static function (string $start, string $end, array $ranges): bool {
            foreach ($ranges as [$bStart, $bEnd]) {
                if ($start <= $bEnd && $end >= $bStart) {
                    return true;
                }
            }

            return false;
        };

        for ($attempt = 0; $attempt < $count; $attempt++) {
            $employee = $employees[($seedIndex + $attempt) % $count];
            $empId = (int) $employee->id;
            $busy = $busyByEmployee[$empId] ?? [];

            // Prefer day 2 onward; walk the month looking for a free window.
            $cursor = $monthStart->copy()->addDay();
            while ($cursor->copy()->addDays($durationDays - 1)->lte($monthEnd)) {
                $start = $cursor->copy();
                $end = $cursor->copy()->addDays($durationDays - 1);
                $startStr = $start->toDateString();
                $endStr = $end->toDateString();

                if (! $overlaps($startStr, $endStr, $busy)) {
                    return [$employee, $start, $end];
                }

                $cursor->addDay();
            }
        }

        return null;
    }

    protected function seedAuditReports(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->first();
        $officer = User::query()->where('email', 'officer1@bynnasaudit.com')->first() ?? $admin;
        $shakhas = Shakha::query()->orderBy('id')->limit(12)->get();

        foreach ($shakhas as $i => $shakha) {
            $isDraft = $i % 3 !== 0;
            AuditReport::query()->updateOrCreate(
                [
                    'shakha_id' => $shakha->id,
                    'report_month' => 7,
                    'report_year' => 2026,
                    'memo_no' => 'DEMO/AUD/2026/'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                ],
                [
                    'user_id' => ($i % 2 === 0 ? $admin?->id : $officer?->id),
                    'status' => $isDraft ? AuditReport::STATUS_DRAFT : AuditReport::STATUS_COMPLETED,
                    'report_date' => now()->subDays(10 - $i)->toDateString(),
                    'control_rating' => ['Satisfactory', 'Minor', 'Medium', 'Major'][$i % 4],
                    'shakha_display_name' => $shakha->name,
                    'area_display_name' => $shakha->area?->name,
                    'audit_period_label' => 'জুলাই ২০২৬',
                    'audit_start_date' => now()->subDays(20)->toDateString(),
                    'audit_end_date' => now()->subDays(12)->toDateString(),
                    'working_days' => 5 + ($i % 4),
                    'auditor_name' => $officer?->name ?? 'Demo Auditor',
                    'auditor_designation' => 'Audit Officer',
                    'progress_pct' => $isDraft ? 35 + ($i * 5) % 50 : 100,
                    'current_tab' => $isDraft ? 'cover' : 'summary',
                    'last_saved_at' => now(),
                    'completed_at' => $isDraft ? null : now()->subDays(5),
                    'pages_data' => [
                        'demo' => true,
                        'note' => 'Demo seeded report — safe to delete later.',
                    ],
                ]
            );
        }
    }
}
