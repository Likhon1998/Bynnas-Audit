<?php

namespace App\Services;

use App\Models\AuditChecklistSubmission;
use App\Models\AuditReport;
use App\Models\AuditReportReviewEvent;
use App\Models\AuditReportSend;
use App\Models\MonthlyAssignment;
use App\Models\User;
use App\Models\VisitExecution;
use App\Support\AppTime;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One timeline of everything auditors do: writing reports, the review round trip,
 * branch visits, emails, checklists and when they were last signed in.
 */
class AuditorActivityService
{
    public const RANGES = [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 90 days',
        'year' => 'This year',
    ];

    public const TYPES = [
        'started' => ['group' => 'report', 'tone' => 'sky', 'label' => 'Started a report'],
        'completed' => ['group' => 'report', 'tone' => 'teal', 'label' => 'Finished writing'],
        'pdf_stored' => ['group' => 'report', 'tone' => 'slate', 'label' => 'Filed PDF in Report storage'],
        'submitted' => ['group' => 'review', 'tone' => 'violet', 'label' => 'Sent for 1st review'],
        'resubmitted' => ['group' => 'review', 'tone' => 'violet', 'label' => 'Sent for re-review'],
        'returned' => ['group' => 'review', 'tone' => 'amber', 'label' => 'Sent back for changes'],
        'approved' => ['group' => 'review', 'tone' => 'emerald', 'label' => 'Report confirmed'],
        'maker_done' => ['group' => 'review', 'tone' => 'emerald', 'label' => 'Marked review fixes done'],
        'note' => ['group' => 'review', 'tone' => 'slate', 'label' => 'Review note'],
        'visit_started' => ['group' => 'visit', 'tone' => 'orange', 'label' => 'Started a branch visit'],
        'visit_done' => ['group' => 'visit', 'tone' => 'rose', 'label' => 'Finished a branch visit'],
        'visit_delayed' => ['group' => 'visit', 'tone' => 'amber', 'label' => 'Branch visit delayed'],
        'email' => ['group' => 'other', 'tone' => 'sky', 'label' => 'Emailed a report'],
        'checklist' => ['group' => 'other', 'tone' => 'indigo', 'label' => 'Saved a checklist'],
    ];

    public const GROUPS = [
        'report' => 'Writing',
        'review' => 'Review',
        'visit' => 'Visits',
        'other' => 'Email & checklists',
    ];

    /**
     * @return array{key:string, label:string, from:CarbonImmutable, to:CarbonImmutable}
     */
    public function range(?string $key): array
    {
        $key = array_key_exists((string) $key, self::RANGES) ? (string) $key : '30';
        $now = CarbonImmutable::parse(AppTime::now());
        $to = $now->endOfDay();
        $from = match ($key) {
            '7' => $now->subDays(6)->startOfDay(),
            '90' => $now->subDays(89)->startOfDay(),
            'year' => $now->startOfYear(),
            default => $now->subDays(29)->startOfDay(),
        };

        return ['key' => $key, 'label' => self::RANGES[$key], 'from' => $from, 'to' => $to];
    }

    /**
     * @return Collection<int, User>
     */
    public function auditors(): Collection
    {
        return User::query()
            ->permission('audits.create')
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'employee_id', 'is_active', 'is_superadmin']);
    }

    /**
     * Unified activity for the given auditors, newest first.
     *
     * @param  Collection<int, User>  $users
     * @return Collection<int, array<string, mixed>>
     */
    public function events(Collection $users, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($userIds === []) {
            return collect();
        }

        $events = collect();
        $push = function (string $type, int $userId, $at, array $extra = []) use (&$events) {
            $moment = $at instanceof CarbonInterface ? CarbonImmutable::parse($at) : CarbonImmutable::parse((string) $at);
            $events->push(array_merge([
                'type' => $type,
                'group' => self::TYPES[$type]['group'],
                'tone' => self::TYPES[$type]['tone'],
                'label' => self::TYPES[$type]['label'],
                'user_id' => $userId,
                'at' => $moment,
                'date_only' => false,
                'detail' => null,
                'by' => null,
                'body' => null,
                'url' => null,
                'perfect' => false,
                'first_time' => false,
            ], $extra));
        };

        $reportPlace = fn (?AuditReport $report) => $report
            ? trim($report->entityDisplayName().' · '.$report->periodLabel(), ' ·')
            : null;

        $reports = AuditReport::query()
            ->with(['shakha:id,name', 'projectLocation.project'])
            ->whereIn('user_id', $userIds)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to])
                    ->orWhereBetween('completed_at', [$from, $to])
                    ->orWhereBetween('maker_done_at', [$from, $to])
                    ->orWhereBetween('storage_pdf_at', [$from, $to]);
            })
            ->get();

        foreach ($reports as $report) {
            $extra = ['detail' => $reportPlace($report), 'url' => route('audit-review.log.show', $report)];
            if ($report->created_at && $report->created_at->between($from, $to)) {
                $push('started', (int) $report->user_id, $report->created_at, $extra);
            }
            if ($report->completed_at && AppTime::parse($report->completed_at)?->between($from, $to)) {
                $push('completed', (int) $report->user_id, $report->completed_at, $extra);
            }
            if ($report->maker_done_at && AppTime::parse($report->maker_done_at)?->between($from, $to)) {
                $push('maker_done', (int) $report->user_id, $report->maker_done_at, $extra);
            }
            if ($report->storage_pdf_at && AppTime::parse($report->storage_pdf_at)?->between($from, $to)) {
                $push('pdf_stored', (int) $report->user_id, $report->storage_pdf_at, $extra);
            }
        }

        $reviewEvents = AuditReportReviewEvent::query()
            ->with([
                'actor:id,name',
                'report:id,user_id,shakha_id,project_location_id,shakha_display_name,report_month,report_year,review_perfect',
                'report.shakha:id,name',
                'report.projectLocation.project',
            ])
            ->whereBetween('created_at', [$from, $to])
            ->whereHas('report', fn ($q) => $q->whereIn('user_id', $userIds))
            ->get();

        foreach ($reviewEvents as $event) {
            $type = match ($event->action) {
                AuditReportReviewEvent::ACTION_SUBMITTED => 'submitted',
                AuditReportReviewEvent::ACTION_RESUBMITTED => 'resubmitted',
                AuditReportReviewEvent::ACTION_RETURNED => 'returned',
                AuditReportReviewEvent::ACTION_APPROVED => 'approved',
                default => 'note',
            };
            $ownerId = (int) $event->report->user_id;
            $push($type, $ownerId, $event->created_at, [
                'detail' => $reportPlace($event->report),
                'by' => (int) $event->actor_user_id !== $ownerId ? ($event->actor?->name ?: null) : null,
                'body' => in_array($type, ['returned', 'note'], true) ? $this->shortBody($event->body) : null,
                'url' => route('audit-review.log.show', $event->audit_report_id),
                'perfect' => $type === 'approved' && (bool) $event->report->review_perfect,
                'first_time' => $type === 'approved' && (int) ($event->review_round ?: 1) <= 1,
            ]);
        }

        AuditReportSend::query()
            ->with(['report:id,shakha_id,project_location_id,shakha_display_name,report_month,report_year', 'report.shakha:id,name', 'report.projectLocation.project'])
            ->whereIn('sent_by_user_id', $userIds)
            ->where('status', 'sent')
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('sent_at', [$from, $to])
                    ->orWhere(fn ($q2) => $q2->whereNull('sent_at')->whereBetween('created_at', [$from, $to]));
            })
            ->get()
            ->each(function (AuditReportSend $send) use ($push, $reportPlace) {
                $push('email', (int) $send->sent_by_user_id, $send->sent_at ?: $send->created_at, [
                    'detail' => trim(($reportPlace($send->report) ?: 'Report').' → '.$send->to_email),
                ]);
            });

        AuditChecklistSubmission::query()
            ->whereIn('user_id', $userIds)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('saved_at', [$from, $to])
                    ->orWhere(fn ($q2) => $q2->whereNull('saved_at')->whereBetween('updated_at', [$from, $to]));
            })
            ->get()
            ->each(function (AuditChecklistSubmission $sheet) use ($push) {
                $push('checklist', (int) $sheet->user_id, $sheet->saved_at ?: $sheet->updated_at, [
                    'detail' => trim(($sheet->heading ?: 'Checklist').($sheet->shakha_name ? ' · '.$sheet->shakha_name : '')),
                ]);
            });

        $employeeToUser = $users->filter(fn ($u) => $u->employee_id)->mapWithKeys(fn ($u) => [(int) $u->employee_id => (int) $u->id]);
        if ($employeeToUser->isNotEmpty()) {
            $this->visitsFor($employeeToUser->keys()->all())
                ->each(function (MonthlyAssignment $visit) use ($push, $employeeToUser, $from, $to) {
                    $execution = $visit->execution;
                    if (! $execution) {
                        return;
                    }
                    $place = $visit->workItem?->entity_label ?: 'Branch visit';
                    $people = collect([(int) $visit->employee_id])
                        ->concat($visit->visitors->pluck('id')->map(fn ($id) => (int) $id))
                        ->unique()
                        ->map(fn ($employeeId) => $employeeToUser->get($employeeId))
                        ->filter();

                    $start = $execution->actual_start_date;
                    $end = $execution->actual_end_date;
                    foreach ($people as $userId) {
                        if ($start && $start->between($from, $to)) {
                            $push('visit_started', (int) $userId, $start->copy()->setTime(9, 0), ['detail' => $place, 'date_only' => true]);
                        }
                        if ($end && $execution->status === VisitExecution::STATUS_COMPLETED && $end->between($from, $to)) {
                            $push('visit_done', (int) $userId, $end->copy()->setTime(17, 0), ['detail' => $place, 'date_only' => true]);
                        }
                        $delayedOn = $start ?: $visit->start_date;
                        if ($execution->status === VisitExecution::STATUS_DELAYED && $delayedOn && $delayedOn->between($from, $to)) {
                            $push('visit_delayed', (int) $userId, $delayedOn->copy()->setTime(12, 0), ['detail' => $place, 'date_only' => true]);
                        }
                    }
                });
        }

        return $events->sortByDesc(fn ($e) => $e['at']->getTimestamp())->values();
    }

    /**
     * Team dashboard: KPIs, trend chart, one row per auditor and the live feed.
     *
     * @return array<string, mixed>
     */
    public function overview(?string $rangeKey): array
    {
        $range = $this->range($rangeKey);
        $auditors = $this->auditors();
        $events = $this->events($auditors, $range['from'], $range['to']);
        $bins = $this->bins($range);
        $lastSeen = $this->lastSeen($auditors->pluck('id')->all());
        $now = CarbonImmutable::parse(AppTime::now());

        $snapshot = AuditReport::query()
            ->whereIn('user_id', $auditors->pluck('id'))
            ->selectRaw('user_id, status, count(*) as total, avg(progress_pct) as progress')
            ->groupBy('user_id', 'status')
            ->get()
            ->groupBy('user_id');

        $upcoming = $this->upcomingVisits($auditors, $now);

        $trend = array_map(fn ($bin) => ['label' => $bin['label'], 'report' => 0, 'review' => 0, 'visit' => 0, 'other' => 0], $bins);

        $rows = [];
        foreach ($auditors as $auditor) {
            $mine = $events->where('user_id', (int) $auditor->id);
            $spark = array_fill(0, count($bins), 0);
            foreach ($mine as $event) {
                $index = $this->binIndex($bins, $event['at']);
                if ($index !== null) {
                    $spark[$index]++;
                    $trend[$index][$event['group']]++;
                }
            }

            $statusRows = $snapshot->get($auditor->id, collect())->keyBy('status');
            $draftRow = $statusRows->get(AuditReport::STATUS_DRAFT);
            $lastEvent = $mine->first();
            $seen = $lastSeen[(int) $auditor->id] ?? null;
            $lastActive = collect([$seen, $lastEvent['at'] ?? null])->filter()->sortByDesc(fn ($d) => $d->getTimestamp())->first();

            $rows[] = [
                'id' => (int) $auditor->id,
                'name' => $auditor->name,
                'email' => $auditor->email,
                'role' => $auditor->roles->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::headline($n))->join(', '),
                'active' => $auditor->is_active !== false,
                'online' => $seen && $seen->greaterThan($now->subMinutes(5)),
                'last_active' => $lastActive,
                'last_active_label' => $lastActive ? $lastActive->diffForHumans($now, ['short' => true, 'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW]) : 'No activity yet',
                'quiet_days' => $lastActive ? (int) $lastActive->startOfDay()->diffInDays($now->startOfDay()) : null,
                'last_event' => $lastEvent,
                'activity' => $mine->count(),
                'groups' => collect(self::GROUPS)->map(fn ($label, $group) => $mine->where('group', $group)->count())->all(),
                'started' => $mine->where('type', 'started')->count(),
                'sent' => $mine->whereIn('type', ['submitted', 'resubmitted'])->count(),
                'confirmed' => $mine->where('type', 'approved')->count(),
                'returned' => $mine->where('type', 'returned')->count(),
                'visits_done' => $mine->where('type', 'visit_done')->count(),
                'drafts' => (int) ($draftRow->total ?? 0),
                'draft_progress' => $draftRow ? (int) round((float) $draftRow->progress) : null,
                'in_review' => (int) ($statusRows->get(AuditReport::STATUS_IN_REVIEW)->total ?? 0),
                'with_maker' => (int) ($statusRows->get(AuditReport::STATUS_CHANGES_REQUESTED)->total ?? 0),
                'reviewed' => (int) ($statusRows->get(AuditReport::STATUS_REVIEWED)->total ?? 0),
                'next_visit' => $upcoming[(int) $auditor->id] ?? null,
                'spark' => $spark,
            ];
        }

        usort($rows, fn ($a, $b) => [$b['online'], $b['activity'], $a['name']] <=> [$a['online'], $a['activity'], $b['name']]);

        $trendMax = max(1, ...array_map(fn ($t) => $t['report'] + $t['review'] + $t['visit'] + $t['other'], $trend));
        $names = $auditors->pluck('name', 'id');

        return [
            'range' => $range,
            'ranges' => self::RANGES,
            'groups' => self::GROUPS,
            'kpis' => [
                'auditors' => $auditors->count(),
                'active' => collect($rows)->where('activity', '>', 0)->count(),
                'online' => collect($rows)->where('online', true)->count(),
                'events' => $events->count(),
                'started' => $events->where('type', 'started')->count(),
                'sent' => $events->whereIn('type', ['submitted', 'resubmitted'])->count(),
                'confirmed' => $events->where('type', 'approved')->count(),
                'returned' => $events->where('type', 'returned')->count(),
                'visits_done' => $events->where('type', 'visit_done')->count(),
                'emails' => $events->where('type', 'email')->count(),
            ],
            'trend' => $trend,
            'trend_max' => $trendMax,
            'rows' => $rows,
            'feed' => $events->take(30)->map(fn ($e) => $e + ['who' => $names[$e['user_id']] ?? 'Auditor'])->values()->all(),
        ];
    }

    /**
     * Everything one auditor did in the range, plus their reports and visits.
     *
     * @return array<string, mixed>
     */
    public function profile(User $auditor, ?string $rangeKey): array
    {
        $range = $this->range($rangeKey);
        $auditor->loadMissing('roles:id,name', 'employee');
        $users = collect([$auditor]);
        $events = $this->events($users, $range['from'], $range['to']);
        $bins = $this->bins($range);
        $now = CarbonImmutable::parse(AppTime::now());
        $seen = $this->lastSeen([(int) $auditor->id])[(int) $auditor->id] ?? null;

        $spark = array_fill(0, count($bins), ['label' => '', 'report' => 0, 'review' => 0, 'visit' => 0, 'other' => 0]);
        foreach ($bins as $i => $bin) {
            $spark[$i]['label'] = $bin['label'];
        }
        foreach ($events as $event) {
            $index = $this->binIndex($bins, $event['at']);
            if ($index !== null) {
                $spark[$index][$event['group']]++;
            }
        }

        $reports = AuditReport::query()
            ->with(['shakha:id,name', 'projectLocation.project', 'reviewer:id,name'])
            ->where('user_id', $auditor->id)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        $visits = $auditor->employee_id
            ? $this->visitsFor([(int) $auditor->employee_id])
                ->filter(fn (MonthlyAssignment $v) => $v->start_date && $v->start_date->between($range['from']->subDays(0), $now->addDays(45)))
                ->sortBy('start_date')
                ->values()
            : collect();

        $byDay = $events->groupBy(fn ($e) => $e['at']->toDateString());

        return [
            'range' => $range,
            'ranges' => self::RANGES,
            'groups' => self::GROUPS,
            'online' => $seen && $seen->greaterThan($now->subMinutes(5)),
            'last_seen' => $seen,
            'kpis' => [
                'events' => $events->count(),
                'started' => $events->where('type', 'started')->count(),
                'completed' => $events->where('type', 'completed')->count(),
                'sent' => $events->whereIn('type', ['submitted', 'resubmitted'])->count(),
                'returned' => $events->where('type', 'returned')->count(),
                'confirmed' => $events->where('type', 'approved')->count(),
                'visits_done' => $events->where('type', 'visit_done')->count(),
                'emails' => $events->where('type', 'email')->count(),
                'active_days' => $byDay->count(),
            ],
            'trend' => $spark,
            'trend_max' => max(1, ...array_map(fn ($t) => $t['report'] + $t['review'] + $t['visit'] + $t['other'], $spark)),
            'days' => $byDay,
            'reports' => $reports,
            'visits' => $visits,
            'totals' => [
                'reports' => AuditReport::query()->where('user_id', $auditor->id)->count(),
                'drafts' => AuditReport::query()->where('user_id', $auditor->id)->where('status', AuditReport::STATUS_DRAFT)->count(),
                'reviewed' => AuditReport::query()->where('user_id', $auditor->id)->where('status', AuditReport::STATUS_REVIEWED)->count(),
            ],
        ];
    }

    /**
     * @param  list<int>  $employeeIds
     * @return Collection<int, MonthlyAssignment>
     */
    private function visitsFor(array $employeeIds): Collection
    {
        return MonthlyAssignment::query()
            ->with(['execution', 'workItem:id,entity_label,activity_type_id', 'workItem.activityType:id,name', 'visitors:id'])
            ->where(function ($q) use ($employeeIds) {
                $q->whereIn('employee_id', $employeeIds)
                    ->orWhereHas('visitors', fn ($v) => $v->whereIn('employees.id', $employeeIds));
            })
            ->get();
    }

    /**
     * @param  Collection<int, User>  $auditors
     * @return array<int, array{place:string, date:CarbonInterface}>
     */
    private function upcomingVisits(Collection $auditors, CarbonImmutable $now): array
    {
        $employeeToUser = $auditors->filter(fn ($u) => $u->employee_id)->mapWithKeys(fn ($u) => [(int) $u->employee_id => (int) $u->id]);
        if ($employeeToUser->isEmpty()) {
            return [];
        }

        $next = [];
        $this->visitsFor($employeeToUser->keys()->all())
            ->filter(fn (MonthlyAssignment $v) => $v->start_date && $v->start_date->greaterThanOrEqualTo($now->startOfDay())
                && ! in_array($v->execution?->status, [VisitExecution::STATUS_COMPLETED, VisitExecution::STATUS_CANCELLED], true))
            ->sortBy('start_date')
            ->each(function (MonthlyAssignment $visit) use (&$next, $employeeToUser) {
                $people = collect([(int) $visit->employee_id])->concat($visit->visitors->pluck('id')->map(fn ($id) => (int) $id));
                foreach ($people as $employeeId) {
                    $userId = $employeeToUser->get($employeeId);
                    if ($userId && ! isset($next[$userId])) {
                        $next[$userId] = ['place' => $visit->workItem?->entity_label ?: 'Branch visit', 'date' => $visit->start_date];
                    }
                }
            });

        return $next;
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, CarbonImmutable>
     */
    private function lastSeen(array $userIds): array
    {
        if ($userIds === [] || config('session.driver') !== 'database') {
            return [];
        }

        try {
            return DB::table(config('session.table', 'sessions'))
                ->whereIn('user_id', $userIds)
                ->selectRaw('user_id, max(last_activity) as seen')
                ->groupBy('user_id')
                ->pluck('seen', 'user_id')
                ->map(fn ($ts) => CarbonImmutable::createFromTimestamp((int) $ts, AppTime::zone()))
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return list<array{label:string, from:CarbonImmutable, to:CarbonImmutable}>
     */
    private function bins(array $range): array
    {
        $bins = [];
        $cursor = $range['from'];

        if ($range['key'] === 'year') {
            while ($cursor->lessThanOrEqualTo($range['to'])) {
                $bins[] = ['label' => $cursor->format('M'), 'from' => $cursor, 'to' => $cursor->endOfMonth()];
                $cursor = $cursor->addMonth()->startOfMonth();
            }

            return $bins;
        }

        $step = $range['key'] === '90' ? 7 : 1;
        while ($cursor->lessThanOrEqualTo($range['to'])) {
            $end = $cursor->addDays($step - 1)->endOfDay();
            $bins[] = [
                'label' => $step === 1 ? $cursor->format('d M') : $cursor->format('d M').' – '.$end->min($range['to'])->format('d M'),
                'from' => $cursor,
                'to' => $end,
            ];
            $cursor = $cursor->addDays($step);
        }

        return $bins;
    }

    private function binIndex(array $bins, CarbonInterface $at): ?int
    {
        foreach ($bins as $i => $bin) {
            if ($at->between($bin['from'], $bin['to'])) {
                return $i;
            }
        }

        return null;
    }

    private function shortBody(?string $body): ?string
    {
        $body = trim((string) $body);

        return $body === '' ? null : \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $body), 140);
    }
}
