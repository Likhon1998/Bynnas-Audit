<?php

namespace App\Services;

use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditReport;
use App\Models\MonthlyAssignment;
use App\Models\ProjectLocation;
use App\Models\Shakha;
use App\Models\User;
use App\Support\AuditChecklistCatalog;
use App\Support\FinancialYear;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VisitAuditWorkService
{
    public function __construct(
        private AuditReportCollaborationService $collaboration,
        private UserAccessService $access,
    ) {}

    /**
     * Ensure a draft audit report exists for this visit assignment (shakha or project location + month).
     */
    public function ensureDraftForAssignment(MonthlyAssignment $assignment, User $user): AuditReport
    {
        $assignment->loadMissing(['workItem.schedulable', 'workItem']);

        $item = $assignment->workItem;
        if (! $item) {
            throw new InvalidArgumentException('Visit work item not found.');
        }

        $type = (string) $item->schedulable_type;
        $entityId = (int) ($item->schedulable_id ?? 0);
        if ($entityId < 1 || ! in_array($type, [Shakha::class, ProjectLocation::class], true)) {
            throw new InvalidArgumentException('Checklist/report work is only available for shakha or project-location visits.');
        }

        [$month, $year] = $this->periodForWorkItem($item->fy_label, (int) $item->month_index);

        if ($type === Shakha::class) {
            if (! $this->access->canStartReportForShakha($user, $entityId, $month, $year)
                && ! $user->can('audits.manage')
                && ! $user->isSuperAdmin()) {
                throw new InvalidArgumentException('You are not assigned to start a report for this shakha.');
            }
        } else {
            if (! $this->access->canStartReportForProjectLocation($user, $entityId, $month, $year)
                && ! $user->can('audits.manage')
                && ! $user->isSuperAdmin()) {
                throw new InvalidArgumentException('You are not assigned to start a report for this project location.');
            }
        }

        $existing = $this->collaboration->findPeriodReportForSchedulable($type, $entityId, $month, $year);
        if ($existing) {
            if (! $this->collaboration->userMayOpenPeriodReport($existing, $user, $type, $entityId, $month, $year)) {
                throw new InvalidArgumentException('A report for this place and month already exists. Open it from Audit Reports instead of starting a new one.');
            }

            if (! $existing->monthly_assignment_id) {
                $existing->forceFill(['monthly_assignment_id' => $assignment->id])->save();
            }

            if (app(AuditReportReviewService::class)->isEditableByMaker($existing)) {
                $teamIds = $this->collaboration->visitorUserIdsForSchedulablePeriod($type, $entityId, $month, $year);
                $this->collaboration->syncCollaborators($existing, $teamIds, $user);
            }

            return $existing->refresh()->load([
                'shakha.area',
                'projectLocation.project',
                'collaborators:id,name',
                'user:id,name',
            ]);
        }

        $linked = AuditReport::query()
            ->where('monthly_assignment_id', $assignment->id)
            ->latest('id')
            ->first();

        if ($linked && $this->collaboration->userMayOpenPeriodReport($linked, $user, $type, $entityId, $month, $year)) {
            return $linked->load([
                'shakha.area',
                'projectLocation.project',
                'collaborators:id,name',
                'user:id,name',
            ]);
        }

        return DB::transaction(function () use ($assignment, $user, $type, $entityId, $month, $year) {
            if ($type === Shakha::class) {
                $report = $this->createShakhaDraft($assignment, $user, $entityId, $month, $year);
            } else {
                $report = $this->createProjectLocationDraft($assignment, $user, $entityId, $month, $year);
            }

            $teamIds = $this->collaboration->visitorUserIdsForSchedulablePeriod($type, $entityId, $month, $year);
            if ($teamIds !== []) {
                $this->collaboration->syncCollaborators($report, $teamIds, $user);
            }

            return $report->refresh()->load([
                'shakha.area',
                'projectLocation.project',
                'collaborators:id,name',
                'user:id,name',
            ]);
        });
    }

    protected function createShakhaDraft(
        MonthlyAssignment $assignment,
        User $user,
        int $shakhaId,
        int $month,
        int $year,
    ): AuditReport {
        $shakha = Shakha::query()->with('area')->findOrFail($shakhaId);
        $display = trim($shakha->name.($shakha->code ? ' ('.$shakha->code.')' : ''));
        $memo = 'অডিট/শাখা - '.($shakha->code ?: $shakha->id).'/'.$year;

        return AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => $shakhaId,
            'project_location_id' => null,
            'monthly_assignment_id' => $assignment->id,
            'report_month' => $month,
            'report_year' => $year,
            'status' => AuditReport::STATUS_DRAFT,
            'current_tab' => 'cover',
            'progress_pct' => 0,
            'last_saved_at' => now(),
            'memo_no' => $memo,
            'report_date' => now('Asia/Dhaka')->toDateString(),
            'shakha_display_name' => $display,
            'area_display_name' => (string) ($shakha->area?->name ?? ''),
            'auditor_name' => $user->name,
            'pages_data' => $this->defaultPagesData($memo, $user->name, $display, (string) ($shakha->area?->name ?? '')),
        ]);
    }

    protected function createProjectLocationDraft(
        MonthlyAssignment $assignment,
        User $user,
        int $locationId,
        int $month,
        int $year,
    ): AuditReport {
        $location = ProjectLocation::query()->with('project')->findOrFail($locationId);
        $projectName = trim((string) ($location->project?->name ?? ''));
        $place = trim((string) ($location->name ?? ''));
        $display = trim($projectName.($projectName !== '' && $place !== '' ? ' — ' : '').$place) ?: 'Project';
        $area = trim((string) ($location->division ?? ''));
        $memo = 'অডিট/প্রকল্প - '.($location->id).'/'.$year;

        return AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => null,
            'project_location_id' => $locationId,
            'monthly_assignment_id' => $assignment->id,
            'report_month' => $month,
            'report_year' => $year,
            'status' => AuditReport::STATUS_DRAFT,
            'current_tab' => 'cover',
            'progress_pct' => 0,
            'last_saved_at' => now(),
            'memo_no' => $memo,
            'report_date' => now('Asia/Dhaka')->toDateString(),
            'shakha_display_name' => $display,
            'area_display_name' => $area,
            'auditor_name' => $user->name,
            'pages_data' => $this->defaultPagesData($memo, $user->name, $display, $area),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultPagesData(string $memo, string $auditor, string $display, string $area): array
    {
        return [
            'meta' => [
                'tabs_done' => [
                    'cover' => false,
                    'page2' => false,
                    'page3' => false,
                    'page4' => false,
                ],
                'active_tab' => 'cover',
                'checklist_first' => true,
            ],
            'cover' => [
                'memo_no' => $memo,
                'report_date' => now('Asia/Dhaka')->toDateString(),
                'auditor_name' => $auditor,
                'shakha_display_name' => $display,
                'area_display_name' => $area,
            ],
            'page4' => [
                'reportBlocks' => [],
            ],
        ];
    }

    /**
     * @return array{0:int,1:int} [month, year]
     */
    public function periodForWorkItem(string $fyLabel, int $monthIndex): array
    {
        $fy = FinancialYear::fromLabel($fyLabel);
        $months = $fy->months();
        $meta = $months[$monthIndex] ?? null;
        if (! $meta) {
            throw new InvalidArgumentException('Invalid month index for financial year.');
        }

        return [(int) $meta['month'], (int) $meta['year']];
    }

    /**
     * @return list<string>
     */
    public function requiredFormatCodes(): array
    {
        // Catalog codes available to pick from (not all are required per visit).
        return ['format-1', 'format-2', 'format-3', 'format-4', 'format-5'];
    }

    public function ensureFormatsExist(): void
    {
        foreach (AuditChecklistCatalog::all() as $def) {
            AuditChecklistFormat::query()->updateOrCreate(
                ['code' => $def['code']],
                [
                    'format_number' => $def['number'],
                    'heading' => $def['heading'],
                    'org_name' => $def['org_name'],
                    'dept_name' => $def['dept_name'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Progress for optional checklist formats the auditor selected.
     * Empty selection is ready (checklist never required to write the report).
     *
     * @return array{ready:bool,required:int,done:int,missing:list<string>,done_codes:list<string>,needs_selection:bool}
     */
    public function checklistProgress(AuditReport $report): array
    {
        $codes = $this->selectedFormatCodes($report);
        if ($codes === []) {
            return [
                'ready' => true,
                'required' => 0,
                'done' => 0,
                'missing' => [],
                'done_codes' => [],
                'needs_selection' => true,
            ];
        }

        $doneCodes = AuditChecklistSubmission::query()
            ->where('audit_report_id', $report->id)
            ->whereNotNull('saved_at')
            ->where('status', 'evidence')
            ->with('format:id,code')
            ->get()
            ->map(fn (AuditChecklistSubmission $s) => (string) ($s->format?->code ?: ''))
            ->filter(fn (string $c) => in_array($c, $codes, true))
            ->unique()
            ->values()
            ->all();

        $missing = array_values(array_diff($codes, $doneCodes));

        return [
            'ready' => $missing === [],
            'required' => count($codes),
            'done' => count($doneCodes),
            'missing' => $missing,
            'done_codes' => $doneCodes,
            'needs_selection' => false,
        ];
    }

    /**
     * @return list<string>
     */
    protected function selectedFormatCodes(AuditReport $report): array
    {
        $fromPivot = $report->checklistFormats()
            ->pluck('code')
            ->map(fn ($c) => (string) $c)
            ->filter()
            ->values()
            ->all();

        if ($fromPivot !== []) {
            return array_values(array_intersect($fromPivot, $this->requiredFormatCodes()));
        }

        $pages = (array) $report->pages_data;
        $meta = (array) ($pages['meta'] ?? []);
        $selected = array_values(array_filter(array_map(
            'strval',
            (array) ($meta['checklist_format_codes'] ?? [])
        )));

        return array_values(array_intersect($selected, $this->requiredFormatCodes()));
    }
}
