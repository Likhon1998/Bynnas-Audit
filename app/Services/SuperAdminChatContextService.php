<?php

namespace App\Services;

use App\Models\Area;
use App\Models\AuditFinding;
use App\Models\AuditPlan;
use App\Models\AuditReport;
use App\Models\MonthlyAssignment;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\ShakhaEmployee;
use App\Models\ShakhaRiskAssessment;
use App\Models\User;
use App\Models\VisitExecution;
use App\Support\FinancialYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SuperAdminChatContextService
{
    public const INTENTS = [
        'ops.overview',
        'reports.status',
        'findings.summary',
        'annual_plan.summary',
        'visits.performance',
        'risk.summary',
        'kpi.summary',
        'shakhas.directory',
        'employees.directory',
        'users.summary',
    ];

    /** @var list<string> */
    private const PROHIBITED_KEYS = [
        'password', 'remember_token', 'token', 'secret', 'api_key', 'app_key',
        'mail_password', 'pages_data', 'payload', 'observation', 'responsible_staff_name',
        'remarks', 'notes', 'body', 'error_message', 'photo_path', 'stored_path',
    ];

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(string $intent, array $filters): array
    {
        abort_unless(in_array($intent, self::INTENTS, true), 422, 'Unsupported chatbot intent.');

        $context = match ($intent) {
            'ops.overview' => $this->overview(),
            'reports.status' => $this->reports($filters),
            'findings.summary' => $this->findings($filters),
            'annual_plan.summary' => $this->annualPlan($filters),
            'visits.performance' => $this->visits($filters),
            'risk.summary' => $this->risks($filters),
            'kpi.summary' => $this->kpis($filters),
            'shakhas.directory' => $this->shakhas($filters),
            'employees.directory' => $this->employees($filters),
            'users.summary' => $this->users(),
        };

        return $this->sanitize($context);
    }

    /** @return array<string, mixed> */
    private function overview(): array
    {
        return [
            'as_of' => now('Asia/Dhaka')->toIso8601String(),
            'shakhas' => ['active' => Shakha::query()->where('status', 'active')->count(), 'total' => Shakha::query()->count()],
            'areas' => Area::query()->count(),
            'audit_reports' => [
                'total' => AuditReport::query()->count(),
                'completed' => AuditReport::query()->completed()->count(),
                'draft' => AuditReport::query()->drafts()->count(),
            ],
            'findings' => AuditFinding::query()->count(),
            'branch_employees' => ShakhaEmployee::query()->count(),
            'users' => ['active' => User::query()->where('is_active', true)->count(), 'total' => User::query()->count()],
        ];
    }

    /** @param array<string, mixed> $filters */
    private function reports(array $filters): array
    {
        $query = AuditReport::query()->with('shakha:id,name,code');
        if (($filters['date_basis'] ?? null) === 'completed') {
            $this->applyDatePeriod($query, $filters, 'completed_at');
            $query->completed();
        } else {
            $this->applyPeriod($query, $filters, 'report_month', 'report_year');
        }
        $this->applyShakha($query, $filters);
        if (in_array($filters['status'] ?? null, [AuditReport::STATUS_DRAFT, AuditReport::STATUS_COMPLETED], true)) {
            $query->where('status', $filters['status']);
        }

        $rows = (clone $query)->latest('id')->limit($this->limit())->get([
            'id', 'shakha_id', 'status', 'progress_pct', 'control_rating',
            'report_month', 'report_year', 'completed_at',
        ]);

        return [
            'total' => (clone $query)->count(),
            'by_status' => (clone $query)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->all(),
            'reports' => $rows->map(fn (AuditReport $report) => [
                'reference' => $report->id,
                'shakha' => $report->shakha?->name,
                'shakha_code' => $report->shakha?->code,
                'period' => $report->periodLabel(),
                'status' => $report->status,
                'progress_percent' => $report->progress_pct,
                'control_rating' => $report->control_rating,
                'completed_at' => $report->completed_at?->toIso8601String(),
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function findings(array $filters): array
    {
        $query = AuditFinding::query()->with(['shakha:id,name,code', 'indicator:id,indicator_code,title,category,risk_rating']);
        $this->applyPeriod($query, $filters, 'audit_month', 'audit_year');
        $this->applyShakha($query, $filters);
        $summary = (clone $query)->selectRaw(
            'COUNT(*) as finding_count, COALESCE(SUM(amount), 0) as amount_total, '
            .'COALESCE(SUM(sample_size_checked), 0) as samples_checked, '
            .'COALESCE(SUM(irregularity_count), 0) as irregularities, '
            .'COUNT(DISTINCT audit_indicator_id) as indicator_count, COUNT(DISTINCT shakha_id) as shakha_count, '
            ."COALESCE(SUM(CASE WHEN irregularity_count > 0 OR amount > 0 OR (observation IS NOT NULL AND observation <> '') THEN 1 ELSE 0 END), 0) as objected_cell_count"
        )->first();
        $rows = $query->latest('id')->limit($this->limit())->get([
            'id', 'shakha_id', 'audit_indicator_id', 'audit_month', 'audit_year',
            'amount', 'sample_size_checked', 'irregularity_count',
        ]);

        return [
            'finding_count' => (int) ($summary?->finding_count ?? 0),
            'matrix_cell_count' => (int) ($summary?->finding_count ?? 0),
            'objected_cell_count' => (int) ($summary?->objected_cell_count ?? 0),
            'indicator_count' => (int) ($summary?->indicator_count ?? 0),
            'shakha_count' => (int) ($summary?->shakha_count ?? 0),
            'amount_total' => round((float) ($summary?->amount_total ?? 0), 2),
            'samples_checked' => (int) ($summary?->samples_checked ?? 0),
            'irregularities' => (int) ($summary?->irregularities ?? 0),
            'rows_returned' => $rows->count(),
            'findings' => $rows->map(fn (AuditFinding $finding) => [
                'indicator_code' => $finding->indicator?->indicator_code,
                'heading' => $finding->indicator?->title,
                'category' => $finding->indicator?->category,
                'risk_rating' => $finding->indicator?->risk_rating,
                'shakha' => $finding->shakha?->name,
                'amount' => (float) $finding->amount,
                'sample_size' => $finding->sample_size_checked,
                'irregularity_count' => $finding->irregularity_count,
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function annualPlan(array $filters): array
    {
        $fy = $this->fy($filters);
        $requestedFy = trim((string) ($filters['fy'] ?? ''));
        $plan = AuditPlan::query()->where('fy_label', $fy)->first();
        if (! $plan && $requestedFy === '') {
            $plan = AuditPlan::query()->latest('start_date')->first();
        }
        if (! $plan) {
            return ['fy' => $fy, 'message' => 'No annual audit plan exists.'];
        }
        $builder = new AnnualAuditReportBuilder($plan);

        return [
            'fy' => $plan->fy_label,
            'status' => $plan->status,
            'generated_at' => $plan->generated_at?->toIso8601String(),
            'summary' => $builder->kpis(),
            'categories' => array_values($builder->totalsByCategory()),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function visits(array $filters): array
    {
        $month = $this->month($filters);
        $year = $this->year($filters);
        $query = VisitExecution::query()->whereHas('assignment', function (Builder $query) use ($month, $year) {
            $query->whereMonth('start_date', $month)->whereYear('start_date', $year);
        });

        return [
            'period' => sprintf('%04d-%02d', $year, $month),
            'assignments' => MonthlyAssignment::query()->whereMonth('start_date', $month)->whereYear('start_date', $year)->count(),
            'executions' => $query->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->all(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function risks(array $filters): array
    {
        $query = ShakhaRiskAssessment::query()->with('shakha:id,name,code');
        $this->applyPeriod($query, $filters, 'assessment_month', 'assessment_year');
        $this->applyShakha($query, $filters);
        $total = (clone $query)->count();
        $byCategory = (clone $query)->select('risk_category', DB::raw('count(*) as total'))
            ->groupBy('risk_category')
            ->pluck('total', 'risk_category')
            ->all();
        $rows = $query->orderByDesc('total_weighted_score')->limit($this->limit())->get([
            'id', 'shakha_id', 'assessment_month', 'assessment_year',
            'total_weighted_score', 'risk_category',
        ]);

        return [
            'total' => $total,
            'by_category' => $byCategory,
            'rows_returned' => $rows->count(),
            'assessments' => $rows->map(fn (ShakhaRiskAssessment $risk) => [
                'shakha' => $risk->shakha?->name,
                'shakha_code' => $risk->shakha?->code,
                'period' => $risk->periodLabel(),
                'score' => $risk->total_weighted_score,
                'category' => $risk->risk_category,
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function kpis(array $filters): array
    {
        $fy = $this->fy($filters);
        $query = ShakhaAnnualKpi::query()->with('shakha:id,name,code')->where('fy_label', $fy);
        $this->applyShakha($query, $filters);
        $summary = (clone $query)->selectRaw(
            'COUNT(*) as shakhas_with_kpi, COALESCE(SUM(fo_count), 0) as field_officers, '
            .'COALESCE(SUM(total_samities), 0) as samities, COALESCE(SUM(total_members), 0) as members, '
            .'COALESCE(SUM(total_borrowers), 0) as borrowers, '
            .'COALESCE(SUM(total_od_borrowers), 0) as overdue_borrowers, '
            .'COALESCE(SUM(savings_balance), 0) as savings_balance, '
            .'COALESCE(SUM(loan_outstanding), 0) as loan_outstanding, '
            .'COALESCE(SUM(total_od_taka), 0) as overdue_amount, '
            .'COALESCE(SUM(surplus_deficit_fy), 0) as surplus_deficit'
        )->first();
        $rows = $query->limit($this->limit())->get([
            'id', 'shakha_id', 'fy_label', 'fo_count', 'total_samities', 'total_members',
            'total_borrowers', 'total_od_borrowers', 'savings_balance', 'loan_outstanding',
            'total_od_taka', 'surplus_deficit_fy',
        ]);

        return [
            'fy' => $fy,
            'shakhas_with_kpi' => (int) ($summary?->shakhas_with_kpi ?? 0),
            'rows_returned' => $rows->count(),
            'totals' => [
                'field_officers' => (int) ($summary?->field_officers ?? 0),
                'samities' => (int) ($summary?->samities ?? 0),
                'members' => (int) ($summary?->members ?? 0),
                'borrowers' => (int) ($summary?->borrowers ?? 0),
                'overdue_borrowers' => (int) ($summary?->overdue_borrowers ?? 0),
                'savings_balance' => round((float) ($summary?->savings_balance ?? 0), 2),
                'loan_outstanding' => round((float) ($summary?->loan_outstanding ?? 0), 2),
                'overdue_amount' => round((float) ($summary?->overdue_amount ?? 0), 2),
                'surplus_deficit' => round((float) ($summary?->surplus_deficit ?? 0), 2),
            ],
            'shakhas' => $rows->map(fn (ShakhaAnnualKpi $kpi) => [
                'name' => $kpi->shakha?->name,
                'code' => $kpi->shakha?->code,
                'members' => $kpi->total_members,
                'borrowers' => $kpi->total_borrowers,
                'overdue_borrowers' => $kpi->total_od_borrowers,
                'savings_balance' => (float) $kpi->savings_balance,
                'loan_outstanding' => (float) $kpi->loan_outstanding,
                'overdue_amount' => (float) $kpi->total_od_taka,
                'surplus_deficit' => (float) $kpi->surplus_deficit_fy,
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function shakhas(array $filters): array
    {
        $query = Shakha::query()->with('area:id,name,division');
        $term = trim((string) (($filters['shakha'] ?? null) ?: ($filters['search'] ?? null)));
        if ($term !== '') {
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhereHas('area', fn (Builder $area) => $area
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('division', 'like', "%{$term}%"));
            });
        }
        $total = (clone $query)->count();
        $rows = $query->orderBy('name')->limit($this->limit())->get(['id', 'area_id', 'name', 'code', 'status', 'opening_date']);

        return [
            'total_matches' => $total,
            'rows_returned' => $rows->count(),
            'shakhas' => $rows->map(fn (Shakha $shakha) => [
                'name' => $shakha->name,
                'code' => $shakha->code,
                'status' => $shakha->status,
                'area' => $shakha->area?->name,
                'division' => $shakha->area?->division,
                'opening_date' => $shakha->opening_date?->toDateString(),
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function employees(array $filters): array
    {
        $includeContacts = (bool) ($filters['include_contacts'] ?? false);
        $query = ShakhaEmployee::query()->with('shakha.area:id,name,division');
        $this->applySearch($query, $filters, ['name', 'employee_code', 'designation', 'email', 'phone']);
        $this->applyShakha($query, $filters);
        $total = (clone $query)->count();
        $rows = $query->orderBy('name')->limit($this->limit())->get([
            'id', 'shakha_id', 'employee_code', 'name', 'designation', 'phone', 'email', 'status',
        ]);

        return [
            'total_matches' => $total,
            'rows_returned' => $rows->count(),
            'employees' => $rows->map(function (ShakhaEmployee $employee) use ($includeContacts) {
                $item = [
                    'employee_code' => $employee->employee_code,
                    'name' => $employee->name,
                    'designation' => $employee->designation,
                    'status' => $employee->status,
                    'shakha' => $employee->shakha?->name,
                    'area' => $employee->shakha?->area?->name,
                ];
                if ($includeContacts) {
                    $item['phone'] = $employee->phone;
                    $item['email'] = $employee->email;
                }

                return $item;
            })->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function users(): array
    {
        $users = User::query()->with('roles:id,name')->get(['id', 'is_active']);

        return [
            'total' => $users->count(),
            'active' => $users->where('is_active', true)->count(),
            'inactive' => $users->where('is_active', false)->count(),
            'by_role' => $users->flatMap(fn (User $user) => $user->roles->pluck('name'))->countBy()->all(),
        ];
    }

    /** @param Builder<*> $query @param array<string,mixed> $filters */
    private function applyPeriod(Builder $query, array $filters, string $monthColumn, string $yearColumn): void
    {
        if (($filters['period_scope'] ?? null) === 'all') {
            return;
        }

        if (($filters['period_scope'] ?? null) === 'year') {
            $query->where($yearColumn, $this->year($filters));

            return;
        }

        $query->where($monthColumn, $this->month($filters))->where($yearColumn, $this->year($filters));
    }

    /** @param Builder<*> $query @param array<string,mixed> $filters */
    private function applyDatePeriod(Builder $query, array $filters, string $column): void
    {
        if (($filters['period_scope'] ?? null) === 'all') {
            return;
        }

        $query->whereYear($column, $this->year($filters));
        if (($filters['period_scope'] ?? null) !== 'year') {
            $query->whereMonth($column, $this->month($filters));
        }
    }

    /** @param Builder<*> $query @param array<string,mixed> $filters */
    private function applyShakha(Builder $query, array $filters): void
    {
        $term = trim((string) ($filters['shakha'] ?? ''));
        if ($term === '') {
            return;
        }
        $query->whereHas('shakha', fn (Builder $q) => $q->where(fn (Builder $inner) => $inner
            ->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%")));
    }

    /** @param Builder<*> $query @param array<string,mixed> $filters @param list<string> $columns */
    private function applySearch(Builder $query, array $filters, array $columns): void
    {
        $term = trim((string) ($filters['search'] ?? ''));
        if ($term === '') {
            return;
        }
        $query->where(function (Builder $inner) use ($columns, $term) {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? $inner->where($column, 'like', "%{$term}%")
                    : $inner->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    private function month(array $filters): int
    {
        return max(1, min(12, (int) ($filters['month'] ?? now('Asia/Dhaka')->month)));
    }

    private function year(array $filters): int
    {
        return max(2000, min(2100, (int) ($filters['year'] ?? now('Asia/Dhaka')->year)));
    }

    private function fy(array $filters): string
    {
        $fy = trim((string) ($filters['fy'] ?? ''));

        return preg_match('/^\d{4}-\d{4}$/', $fy) ? $fy : FinancialYear::current(now('Asia/Dhaka'))->label;
    }

    private function limit(): int
    {
        return max(5, min(100, (int) config('services.gemini.max_rows', 50)));
    }

    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return is_string($value) ? mb_substr($value, 0, 500) : $value;
        }

        $clean = [];
        foreach ($value as $key => $item) {
            if (is_string($key) && in_array(strtolower($key), self::PROHIBITED_KEYS, true)) {
                continue;
            }
            $clean[$key] = $this->sanitize($item);
        }

        return $clean;
    }
}
