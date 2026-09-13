<?php

namespace App\Services;

use App\Models\AuditReport;
use App\Models\AuditReportReviewAnnotation;
use App\Models\AuditReportReviewEvent;
use App\Models\AuditReportReviewSnapshot;
use App\Models\AuditReviewerAssignment;
use App\Models\User;
use App\Support\AppTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuditReportReviewService
{
    public function assignmentForAuditor(int $auditorUserId): ?AuditReviewerAssignment
    {
        return AuditReviewerAssignment::query()
            ->with(['reviewer:id,name,email'])
            ->where('auditor_user_id', $auditorUserId)
            ->first();
    }

    public function reviewerForReport(AuditReport $report): ?User
    {
        if ($report->reviewer_user_id) {
            return User::query()->find($report->reviewer_user_id);
        }

        $assignment = $this->assignmentForAuditor((int) $report->user_id);

        return $assignment?->reviewer;
    }

    public function canReview(User $user, AuditReport $report): bool
    {
        if (! in_array($report->status, [
            AuditReport::STATUS_IN_REVIEW,
            AuditReport::STATUS_CHANGES_REQUESTED,
            AuditReport::STATUS_REVIEWED,
        ], true)) {
            return false;
        }

        if ($this->isReviewAdmin($user)) {
            return true;
        }

        if (! $user->can('audits.review')) {
            return false;
        }

        return (int) $report->reviewer_user_id === (int) $user->id;
    }

    public function canActAsReviewer(User $user, AuditReport $report): bool
    {
        if ($report->status !== AuditReport::STATUS_IN_REVIEW) {
            return false;
        }

        // Admin / assigner may step in to review any report.
        if ($this->isReviewAdmin($user)) {
            return true;
        }

        return (int) $report->reviewer_user_id === (int) $user->id
            && $user->can('audits.review');
    }

    /**
     * Superadmin or Assign reviewers permission — may oversee and step into reviews.
     */
    public function isReviewAdmin(User $user): bool
    {
        return $user->canAssignReviewers();
    }

    /**
     * Whether this user sees every report in the review inbox (not only assigned).
     */
    public function seesAllReviewInbox(User $user): bool
    {
        return $this->isReviewAdmin($user);
    }

    /**
     * @return array{inbox:int,ready_to_send:int,returned:int,sent_to_me:int,total:int}
     */
    public function actionCounts(User $user): array
    {
        $inbox = 0;
        $ready = 0;
        $returned = 0;

        if ($user->can('audits.review') || $this->isReviewAdmin($user)) {
            $inboxQuery = AuditReport::query()
                ->where('status', AuditReport::STATUS_IN_REVIEW)
                ->whereNull('review_ready_at');
            $readyQuery = AuditReport::query()
                ->where('status', AuditReport::STATUS_IN_REVIEW)
                ->whereNotNull('review_ready_at')
                ->whereNull('review_sent_to_maker_at');

            if (! $this->seesAllReviewInbox($user)) {
                $inboxQuery->where('reviewer_user_id', $user->id);
                $readyQuery->where('reviewer_user_id', $user->id);
            }

            $inbox = (int) $inboxQuery->count();
            $ready = (int) $readyQuery->count();
        }

        if ($user->can('audits.create') || $user->can('audits.manage')) {
            $returned = (int) AuditReport::query()
                ->where('status', AuditReport::STATUS_CHANGES_REQUESTED)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('collaborators', fn ($c) => $c->where('users.id', $user->id));
                })
                ->count();
        }

        return [
            'inbox' => $inbox,
            'ready_to_send' => $ready,
            'returned' => $returned,
            'sent_to_me' => 0,
            'total' => $inbox + $ready + $returned,
        ];
    }

    /**
     * Monthly review workload for a reviewer, tied to report period month/year.
     *
     * @return array{
     *   month:int,
     *   year:int,
     *   period_label:string,
     *   first_reviews:int,
     *   re_reviews:int,
     *   confirmed:int,
     *   awaiting:int,
     *   reports:list<array{id:int,name:string,round:int,round_label:string,status:string,is_resubmit:bool}>
     * }
     */
    public function monthlyReviewStats(User $user, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?: (int) AppTime::now()->month;
        $year = $year ?: (int) AppTime::now()->year;

        $reportScope = function ($q) use ($user, $month, $year) {
            $q->where('report_month', $month)->where('report_year', $year);
            if (! $this->seesAllReviewInbox($user)) {
                $q->where('reviewer_user_id', $user->id);
            }
        };

        if (! ($user->can('audits.review') || $this->isReviewAdmin($user))) {
            return [
                'month' => $month,
                'year' => $year,
                'period_label' => \Carbon\Carbon::create($year, $month, 1)->timezone('Asia/Dhaka')->format('F Y'),
                'first_reviews' => 0,
                're_reviews' => 0,
                'confirmed' => 0,
                'awaiting' => 0,
                'reports' => [],
            ];
        }

        $snapshots = AuditReportReviewSnapshot::query()
            ->whereHas('report', $reportScope)
            ->get(['id', 'audit_report_id', 'review_round', 'is_resubmit']);

        $first = (int) $snapshots->where('is_resubmit', false)->count();
        $re = (int) $snapshots->where('is_resubmit', true)->count();

        $reports = AuditReport::query()
            ->with(['shakha:id,name', 'user:id,name'])
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->when(
                ! $this->seesAllReviewInbox($user),
                fn ($q) => $q->where('reviewer_user_id', $user->id)
            )
            ->where(function ($q) {
                $q->whereIn('status', [
                    AuditReport::STATUS_IN_REVIEW,
                    AuditReport::STATUS_CHANGES_REQUESTED,
                    AuditReport::STATUS_REVIEWED,
                ])->orWhere('review_round', '>', 0);
            })
            ->orderByDesc('submitted_for_review_at')
            ->orderByDesc('id')
            ->get();

        $confirmed = 0;
        $awaiting = 0;
        $rows = [];

        foreach ($reports as $report) {
            $round = max(1, (int) $report->review_round);
            $isResubmit = $round >= 2;
            if ($report->isReviewed()) {
                $confirmed++;
            }
            if ($report->isInReview() && ! $report->isReviewReady()) {
                $awaiting++;
            }

            $rows[] = [
                'id' => (int) $report->id,
                'name' => $report->entityDisplayName(),
                'maker' => $report->user?->name ?: '—',
                'round' => $round,
                'round_label' => AuditReport::reviewRoundLabel($round),
                'status' => $report->statusLabel(),
                'is_resubmit' => $isResubmit,
            ];
        }

        $periodLabel = \Carbon\Carbon::create($year, $month, 1)->timezone('Asia/Dhaka')->format('F Y');

        return [
            'month' => $month,
            'year' => $year,
            'period_label' => $periodLabel,
            'first_reviews' => $first,
            're_reviews' => $re,
            'confirmed' => $confirmed,
            'awaiting' => $awaiting,
            'reports' => $rows,
        ];
    }

    /**
     * @return list<array{key:string,tone:string,title:string,body:string,count:int,url:string,action:string}>
     */
    public function actionNotifications(User $user): array
    {
        $counts = $this->actionCounts($user);
        $items = [];

        if ($counts['inbox'] > 0) {
            $items[] = [
                'key' => 'inbox',
                'tone' => 'amber',
                'title' => $counts['inbox'] === 1
                    ? '1 report waiting for your review'
                    : $counts['inbox'].' reports waiting for your review',
                'body' => '1st review and re-review (after changes) both appear here. Check the Round badge — they are not the same.',
                'count' => $counts['inbox'],
                'url' => route('audit-review.index', ['tab' => 'inbox']),
                'action' => 'Review now',
            ];
        }

        if ($counts['ready_to_send'] > 0) {
            $items[] = [
                'key' => 'ready_to_send',
                'tone' => 'sky',
                'title' => $counts['ready_to_send'] === 1
                    ? '1 review is ready to send or confirm'
                    : $counts['ready_to_send'].' reviews are ready to send or confirm',
                'body' => 'Send to maker = they fix & resubmit (next round). Confirm = final lock.',
                'count' => $counts['ready_to_send'],
                'url' => route('audit-review.index', ['tab' => 'reviewed']),
                'action' => 'Open Reviewed',
            ];
        }

        if ($counts['returned'] > 0) {
            $items[] = [
                'key' => 'returned',
                'tone' => 'rose',
                'title' => $counts['returned'] === 1
                    ? '1 report needs your fixes'
                    : $counts['returned'].' reports need your fixes',
                'body' => 'Fix marks, tick Done, then Resubmit for re-review (not the same as 1st send).',
                'count' => $counts['returned'],
                'url' => route('audit-review.index', ['tab' => 'returned']),
                'action' => 'Edit & resubmit',
            ];
        }

        if ($items === []) {
            $items[] = [
                'key' => 'clear',
                'tone' => 'emerald',
                'title' => 'You are all caught up',
                'body' => 'No review actions waiting right now.',
                'count' => 0,
                'url' => route('audit-review.index'),
                'action' => 'Refresh',
            ];
        }

        return $items;
    }

    /**
     * Package for the reviewer when opening a report (asks vs changes).
     *
     * @return array{
     *   round:int,
     *   round_label:string,
     *   is_resubmit:bool,
     *   maker_note:?string,
     *   change_summary:list<string>,
     *   prior_asks:list<array<string,mixed>>,
     *   addressed_asks:list<array<string,mixed>>,
     *   current_asks:list<array<string,mixed>>,
     *   timeline:list<array<string,mixed>>
     * }
     */
    public function reviewContext(AuditReport $report): array
    {
        $round = max(1, (int) $report->review_round);
        $snapshot = AuditReportReviewSnapshot::query()
            ->where('audit_report_id', $report->id)
            ->where('review_round', $round)
            ->first();

        $annotations = $report->relationLoaded('reviewAnnotations')
            ? $report->reviewAnnotations
            : $report->reviewAnnotations()->with('user:id,name')->get();

        $priorAsks = $annotations
            ->filter(fn (AuditReportReviewAnnotation $a) => (int) $a->review_round < $round)
            ->values()
            ->map(fn (AuditReportReviewAnnotation $a) => $a->toReviewPayload())
            ->all();

        $addressedIds = collect($snapshot?->addressed_annotation_ids ?? [])->map(fn ($id) => (int) $id)->all();
        $addressedAsks = $annotations
            ->filter(fn (AuditReportReviewAnnotation $a) => in_array((int) $a->id, $addressedIds, true) || $a->addressed_at)
            ->values()
            ->map(fn (AuditReportReviewAnnotation $a) => $a->toReviewPayload())
            ->all();

        $currentAsks = $annotations
            ->filter(fn (AuditReportReviewAnnotation $a) => (int) $a->review_round === $round)
            ->values()
            ->map(fn (AuditReportReviewAnnotation $a) => $a->toReviewPayload())
            ->all();

        $events = $report->relationLoaded('reviewEvents')
            ? $report->reviewEvents
            : $report->reviewEvents()->with('actor:id,name')->get();

        $timeline = $events->sortBy('id')->values()->map(function (AuditReportReviewEvent $event) {
            return [
                'action' => $event->action,
                'label' => $event->actionLabel(),
                'round' => (int) ($event->review_round ?: 1),
                'round_label' => AuditReport::reviewRoundLabel((int) ($event->review_round ?: 1)),
                'body' => $event->body,
                'actor' => $event->actor?->name ?: 'System',
                'at' => AppTime::dateTime($event->created_at),
            ];
        })->all();

        return [
            'round' => $round,
            'round_label' => AuditReport::reviewRoundLabel($round),
            'is_resubmit' => $round >= 2 || (bool) ($snapshot?->is_resubmit),
            'maker_note' => $snapshot?->maker_note,
            'change_summary' => array_values($snapshot?->change_summary ?? []),
            'prior_asks' => $priorAsks,
            'addressed_asks' => $addressedAsks,
            'current_asks' => $currentAsks,
            'timeline' => $timeline,
        ];
    }

    /**
     * @param  list<int|string>  $addressedAnnotationIds
     *
     * @throws ValidationException
     */
    public function submitForReview(
        AuditReport $report,
        User $actor,
        bool $ccSuperadmin = false,
        ?string $note = null,
        string $destination = 'assigned',
        array $addressedAnnotationIds = [],
    ): AuditReport {
        if (! $report->isOwnedBy($actor) && ! $report->isAccessibleBy($actor)) {
            throw ValidationException::withMessages(['report' => 'You cannot submit this report.']);
        }

        if ($report->isReviewed()) {
            throw ValidationException::withMessages(['report' => 'This report is already reviewed and locked.']);
        }

        if ($report->status === AuditReport::STATUS_IN_REVIEW) {
            throw ValidationException::withMessages(['report' => 'This report is already in review.']);
        }

        if (! in_array($report->status, [
            AuditReport::STATUS_DRAFT,
            AuditReport::STATUS_COMPLETED,
            AuditReport::STATUS_CHANGES_REQUESTED,
        ], true)) {
            throw ValidationException::withMessages(['report' => 'This report cannot be sent for review in its current state.']);
        }

        $destination = in_array($destination, ['assigned', 'superadmin'], true) ? $destination : 'assigned';
        $assignment = $this->assignmentForAuditor((int) $report->user_id);
        $superadmin = $this->primarySuperadmin();

        if ($destination === 'superadmin') {
            if (! $superadmin) {
                throw ValidationException::withMessages([
                    'report' => 'No Super Admin account is available to receive this review.',
                ]);
            }
            $reviewerUserId = (int) $superadmin->id;
            $ccSuperadmin = true;
        } else {
            if (! $assignment) {
                throw ValidationException::withMessages([
                    'report' => 'No reviewer is assigned for this auditor. Ask an admin to set Reviewer assignments, or send to Super Admin.',
                ]);
            }
            $reviewerUserId = (int) $assignment->reviewer_user_id;
        }

        $isResubmit = $report->status === AuditReport::STATUS_CHANGES_REQUESTED;
        $addressedIds = collect($addressedAnnotationIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        return DB::transaction(function () use (
            $report,
            $actor,
            $assignment,
            $ccSuperadmin,
            $note,
            $isResubmit,
            $reviewerUserId,
            $destination,
            $superadmin,
            $addressedIds,
        ) {
            $previousRound = max(0, (int) $report->review_round);
            $newRound = $previousRound + 1;
            $pages = (array) ($report->pages_data ?? []);
            $fingerprint = hash('sha256', json_encode($pages));

            $previousSnapshot = $previousRound > 0
                ? AuditReportReviewSnapshot::query()
                    ->where('audit_report_id', $report->id)
                    ->where('review_round', $previousRound)
                    ->first()
                : null;

            $changeSummary = $isResubmit
                ? $this->summarizePagesChanges(
                    is_array($previousSnapshot?->pages_data) ? $previousSnapshot->pages_data : null,
                    $pages
                )
                : [];

            if ($isResubmit && $addressedIds !== []) {
                AuditReportReviewAnnotation::query()
                    ->where('audit_report_id', $report->id)
                    ->whereIn('id', $addressedIds)
                    ->whereNull('addressed_at')
                    ->update(['addressed_at' => now()]);
            }

            $report->update([
                'status' => AuditReport::STATUS_IN_REVIEW,
                'reviewer_user_id' => $reviewerUserId,
                'submitted_for_review_at' => now(),
                'review_round' => $newRound,
                'review_cc_superadmin' => $ccSuperadmin,
                'progress_pct' => max((int) $report->progress_pct, 100),
                'completed_at' => $report->completed_at ?? now(),
                'review_ready_at' => null,
                'review_sent_to_maker_at' => null,
                'reviewed_at' => null,
                'review_perfect' => false,
            ]);

            AuditReportReviewSnapshot::query()->updateOrCreate(
                [
                    'audit_report_id' => $report->id,
                    'review_round' => $newRound,
                ],
                [
                    'is_resubmit' => $isResubmit,
                    'submitted_by' => $actor->id,
                    'pages_fingerprint' => $fingerprint,
                    'pages_data' => $pages,
                    'maker_note' => trim((string) $note) !== '' ? trim((string) $note) : null,
                    'addressed_annotation_ids' => $addressedIds,
                    'change_summary' => $changeSummary,
                ]
            );

            $body = $this->composeSubmitBody($assignment, $ccSuperadmin, $note, $destination, $superadmin, $isResubmit, $newRound);
            if ($isResubmit && $changeSummary !== []) {
                $body .= "\nChanges noted: ".implode('; ', array_slice($changeSummary, 0, 8));
            }
            if ($isResubmit && $addressedIds !== []) {
                $body .= "\nMaker marked ".count($addressedIds).' review ask(s) as done.';
            }

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => $isResubmit
                    ? AuditReportReviewEvent::ACTION_RESUBMITTED
                    : AuditReportReviewEvent::ACTION_SUBMITTED,
                'review_round' => $newRound,
                'body' => $body,
                'meta' => [
                    'is_resubmit' => $isResubmit,
                    'addressed_count' => count($addressedIds),
                    'change_count' => count($changeSummary),
                ],
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    public function primarySuperadmin(): ?User
    {
        $byFlag = User::query()
            ->where('is_active', true)
            ->where('is_superadmin', true)
            ->orderBy('id')
            ->first();
        if ($byFlag) {
            return $byFlag;
        }

        return User::query()
            ->where('is_active', true)
            ->role('superadmin')
            ->orderBy('id')
            ->first();
    }

    /**
     * @throws ValidationException
     */
    public function requestChanges(AuditReport $report, User $actor, string $comment): AuditReport
    {
        $comment = trim($comment);
        if ($comment === '') {
            throw ValidationException::withMessages(['body' => 'Please write what needs to change.']);
        }

        if (! $this->canActAsReviewer($actor, $report)) {
            throw ValidationException::withMessages(['report' => 'You are not the reviewer for this report.']);
        }

        $round = max(1, (int) $report->review_round);

        return DB::transaction(function () use ($report, $actor, $comment, $round) {
            $report->reviewAnnotations()
                ->where(function ($q) {
                    $q->whereNull('review_round')->orWhere('review_round', 0);
                })
                ->update(['review_round' => $round]);

            $report->update([
                'status' => AuditReport::STATUS_CHANGES_REQUESTED,
                'review_sent_to_maker_at' => now(),
                'review_ready_at' => null,
                'reviewed_at' => null,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_RETURNED,
                'review_round' => $round,
                'body' => $comment,
                'meta' => ['via' => 'request_changes'],
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    /**
     * @throws ValidationException
     */
    public function sendToMaker(AuditReport $report, User $actor): AuditReport
    {
        if (! $this->canActAsReviewer($actor, $report)) {
            throw ValidationException::withMessages(['report' => 'You are not the reviewer for this report.']);
        }

        if (! $report->isReviewReady()) {
            throw ValidationException::withMessages(['report' => 'Mark review as done before sending to the maker.']);
        }

        $round = max(1, (int) $report->review_round);
        $markCount = $report->reviewAnnotations()->where('review_round', $round)->count();
        if ($markCount === 0) {
            $markCount = $report->reviewAnnotations()->count();
        }
        $makerName = $report->user?->name ?: 'the maker';

        return DB::transaction(function () use ($report, $actor, $markCount, $makerName, $round) {
            $report->reviewAnnotations()
                ->where(function ($q) {
                    $q->whereNull('review_round')->orWhere('review_round', 0);
                })
                ->update(['review_round' => $round]);

            $report->update([
                'status' => AuditReport::STATUS_CHANGES_REQUESTED,
                'review_sent_to_maker_at' => now(),
                'review_ready_at' => null,
                'reviewed_at' => null,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_RETURNED,
                'review_round' => $round,
                'body' => 'Sent to '.$makerName.' after '.AuditReport::reviewRoundLabel($round)
                    .' with '.$markCount.' mark(s). Maker must fix and resubmit for a new re-review round.',
                'meta' => ['via' => 'send_to_maker', 'mark_count' => $markCount],
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    /**
     * Grant the report as 100% perfect / totally fixed and lock it.
     * Works from inbox or review-ready — skips “send to maker”.
     *
     * @throws ValidationException
     */
    public function grantTotallyFixed(AuditReport $report, User $actor, ?string $note = null): AuditReport
    {
        if (! $this->canActAsReviewer($actor, $report)) {
            throw ValidationException::withMessages(['report' => 'You are not the reviewer for this report.']);
        }

        $round = max(1, (int) $report->review_round);
        $note = trim((string) $note);

        return DB::transaction(function () use ($report, $actor, $note, $round) {
            $report->update([
                'status' => AuditReport::STATUS_REVIEWED,
                'reviewed_at' => now(),
                'review_ready_at' => $report->review_ready_at ?? now(),
                'review_sent_to_maker_at' => null,
                'review_perfect' => true,
                'progress_pct' => 100,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_APPROVED,
                'review_round' => $round,
                'body' => $note !== ''
                    ? $note
                    : 'Totally fixed — granted as 100% perfect and locked after '.AuditReport::reviewRoundLabel($round).'.',
                'meta' => [
                    'via' => 'totally_fixed',
                    'perfect' => true,
                    'progress_pct' => 100,
                ],
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    /**
     * @throws ValidationException
     */
    public function approve(AuditReport $report, User $actor, ?string $note = null): AuditReport
    {
        if (! $this->canActAsReviewer($actor, $report)) {
            throw ValidationException::withMessages(['report' => 'You are not the reviewer for this report.']);
        }

        if (! $report->isReviewReady()) {
            throw ValidationException::withMessages([
                'report' => 'Mark review as done before confirming. Use Review done first.',
            ]);
        }

        $round = max(1, (int) $report->review_round);

        return DB::transaction(function () use ($report, $actor, $note, $round) {
            $report->update([
                'status' => AuditReport::STATUS_REVIEWED,
                'reviewed_at' => now(),
                'review_ready_at' => $report->review_ready_at ?? now(),
                'review_perfect' => false,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_APPROVED,
                'review_round' => $round,
                'body' => trim((string) $note) !== ''
                    ? trim((string) $note)
                    : 'Confirmed after '.AuditReport::reviewRoundLabel($round).' — report locked.',
                'meta' => ['via' => 'approve'],
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    /**
     * Maker ticks the report as done after reviewer granted Totally fixed.
     *
     * @throws ValidationException
     */
    public function acknowledgeByMaker(AuditReport $report, User $actor): AuditReport
    {
        if (! $report->canMakerAcknowledgeDone($actor)) {
            if ($report->isMakerDone()) {
                return $report->fresh(['reviewer', 'user', 'shakha']);
            }

            throw ValidationException::withMessages([
                'report' => 'Done tick is only available when the reviewer marked this report Totally fixed · 100% perfect.',
            ]);
        }

        return DB::transaction(function () use ($report, $actor) {
            $report->update([
                'maker_done_at' => now(),
                'maker_done_by' => $actor->id,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_NOTE,
                'review_round' => max(1, (int) $report->review_round),
                'body' => ($actor->name ?: 'Maker').' marked this report as done · 100% perfect.',
                'meta' => ['via' => 'maker_done'],
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    /**
     * Admin / assigner overview of every auditor report position in the pipeline.
     *
     * @return array{
     *   filters: array{month:?int,year:?int,auditor_id:?int,reviewer_id:?int,position:?string,q:?string},
     *   summary: array<string,int>,
     *   auditors: list<array{id:int,name:string,email:string,counts:array<string,int>,reports:list<array<string,mixed>>}>,
     *   events: list<array<string,mixed>>,
     *   auditor_options: list<array{id:int,name:string}>,
     *   reviewer_options: list<array{id:int,name:string}>,
     *   position_options: array<string,string>
     * }
     */
    public function auditorLog(
        ?int $month = null,
        ?int $year = null,
        ?int $auditorId = null,
        ?int $reviewerId = null,
        ?string $position = null,
        ?string $q = null,
    ): array {
        $positionOptions = [
            'awaiting_submit' => 'Ready — not sent for review',
            'in_review' => 'With reviewer (inbox)',
            'review_ready' => 'Review done — send / confirm',
            'with_maker' => 'Returned — maker fixing',
            'confirmed' => 'Confirmed (locked)',
            'totally_fixed' => 'Totally fixed · 100%',
            'maker_done' => 'Maker marked done',
        ];

        $query = AuditReport::query()
            ->with([
                'shakha:id,name,code',
                'projectLocation.project',
                'user:id,name,email',
                'reviewer:id,name,email',
                'reviewEvents' => fn ($e) => $e->with('actor:id,name')->orderByDesc('id')->limit(3),
            ])
            ->where(function ($q) {
                $q->whereIn('status', [
                    AuditReport::STATUS_COMPLETED,
                    AuditReport::STATUS_IN_REVIEW,
                    AuditReport::STATUS_CHANGES_REQUESTED,
                    AuditReport::STATUS_REVIEWED,
                ])->orWhere('review_round', '>', 0);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($month && $year) {
            $query->where('report_month', $month)->where('report_year', $year);
        }
        if ($auditorId) {
            $query->where('user_id', $auditorId);
        }
        if ($reviewerId) {
            $query->where('reviewer_user_id', $reviewerId);
        }
        if ($q !== null && trim($q) !== '') {
            $term = '%'.trim($q).'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('memo_no', 'like', $term)
                    ->orWhere('shakha_display_name', 'like', $term)
                    ->orWhereHas('shakha', fn ($s) => $s->where('name', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });
        }

        $reports = $query->get();

        // Counts stay stable while a position chip is active (filter the list only).
        $summary = array_fill_keys(array_keys($positionOptions), 0);
        $summary['total'] = 0;
        foreach ($reports as $report) {
            $key = $report->workflowPositionKey();
            if (! isset($summary[$key])) {
                $summary[$key] = 0;
            }
            $summary[$key]++;
            $summary['total']++;
        }

        if ($position && isset($positionOptions[$position])) {
            $reports = $reports->filter(fn (AuditReport $r) => $r->workflowPositionKey() === $position)->values();
        }

        $byAuditor = [];
        $rows = [];

        foreach ($reports as $report) {
            $key = $report->workflowPositionKey();

            $latest = $report->reviewEvents->first();
            $row = [
                'id' => (int) $report->id,
                'name' => $report->entityDisplayName(),
                'period' => $report->periodLabel(),
                'memo_no' => (string) ($report->memo_no ?? ''),
                'auditor_id' => (int) $report->user_id,
                'auditor' => $report->user?->name ?: '—',
                'auditor_email' => $report->user?->email ?: '',
                'reviewer' => $report->reviewer?->name ?: '—',
                'round' => max(0, (int) $report->review_round),
                'round_label' => (int) $report->review_round > 0
                    ? AuditReport::reviewRoundLabel((int) $report->review_round)
                    : 'Not submitted',
                'position' => $key,
                'position_label' => $report->workflowPositionLabel(),
                'status_label' => $report->statusLabel(),
                'updated_at' => AppTime::dateTime($report->updated_at),
                'submitted_at' => AppTime::dateTime($report->submitted_for_review_at, AppTime::DATETIME, ''),
                'last_event' => $latest
                    ? ($latest->actionLabel().($latest->actor?->name ? ' · '.$latest->actor->name : ''))
                    : null,
                'last_event_body' => $latest?->body,
                'url' => route('audit-review.log.show', $report),
            ];
            $rows[] = $row;

            $aid = (int) $report->user_id;
            if (! isset($byAuditor[$aid])) {
                $byAuditor[$aid] = [
                    'id' => $aid,
                    'name' => $report->user?->name ?: 'Unknown',
                    'email' => $report->user?->email ?: '',
                    'counts' => array_fill_keys(array_keys($positionOptions), 0),
                    'reports' => [],
                ];
                $byAuditor[$aid]['counts']['total'] = 0;
            }
            if (! isset($byAuditor[$aid]['counts'][$key])) {
                $byAuditor[$aid]['counts'][$key] = 0;
            }
            $byAuditor[$aid]['counts'][$key]++;
            $byAuditor[$aid]['counts']['total']++;
            $byAuditor[$aid]['reports'][] = $row;
        }

        uasort($byAuditor, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        $eventsQuery = AuditReportReviewEvent::query()
            ->with([
                'actor:id,name',
                'report:id,user_id,reviewer_user_id,shakha_id,project_location_id,shakha_display_name,report_month,report_year,status,review_round,review_perfect,maker_done_at',
                'report.user:id,name',
                'report.shakha:id,name',
            ])
            ->whereHas('report', function ($reportQuery) use ($month, $year, $auditorId, $reviewerId, $q) {
                $reportQuery->where(function ($statusQuery) {
                    $statusQuery->whereIn('status', [
                        AuditReport::STATUS_COMPLETED,
                        AuditReport::STATUS_IN_REVIEW,
                        AuditReport::STATUS_CHANGES_REQUESTED,
                        AuditReport::STATUS_REVIEWED,
                    ])->orWhere('review_round', '>', 0);
                });
                if ($month && $year) {
                    $reportQuery->where('report_month', $month)->where('report_year', $year);
                }
                if ($auditorId) {
                    $reportQuery->where('user_id', $auditorId);
                }
                if ($reviewerId) {
                    $reportQuery->where('reviewer_user_id', $reviewerId);
                }
                if ($q !== null && trim($q) !== '') {
                    $term = '%'.trim($q).'%';
                    $reportQuery->where(function ($inner) use ($term) {
                        $inner->where('memo_no', 'like', $term)
                            ->orWhere('shakha_display_name', 'like', $term)
                            ->orWhereHas('shakha', fn ($s) => $s->where('name', 'like', $term)->orWhere('code', 'like', $term))
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
                    });
                }
            });

        $recentEvents = $eventsQuery
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->filter(function (AuditReportReviewEvent $event) use ($position, $positionOptions) {
                if (! $position || ! isset($positionOptions[$position])) {
                    return true;
                }

                return $event->report && $event->report->workflowPositionKey() === $position;
            })
            ->take(40)
            ->values()
            ->map(function (AuditReportReviewEvent $event) {
                $report = $event->report;

                return [
                    'id' => (int) $event->id,
                    'action' => $event->action,
                    'label' => $event->actionLabel(),
                    'round' => (int) ($event->review_round ?: 1),
                    'round_label' => AuditReport::reviewRoundLabel((int) ($event->review_round ?: 1)),
                    'body' => $event->body,
                    'actor' => $event->actor?->name ?: 'System',
                    'at' => AppTime::dateTime($event->created_at),
                    'report_id' => (int) ($report?->id ?? 0),
                    'report_name' => $report?->entityDisplayName() ?: '—',
                    'auditor' => $report?->user?->name ?: '—',
                    'position_label' => $report?->workflowPositionLabel(),
                    'url' => $report ? route('audit-review.log.show', $report) : null,
                ];
            })
            ->all();

        $auditorOptions = User::query()
            ->permission('audits.create')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => (int) $u->id, 'name' => $u->name])
            ->all();

        $reviewerOptions = User::query()
            ->permission('audits.review')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => (int) $u->id, 'name' => $u->name])
            ->all();

        return [
            'filters' => [
                'month' => $month,
                'year' => $year,
                'auditor_id' => $auditorId,
                'reviewer_id' => $reviewerId,
                'position' => $position,
                'q' => $q,
            ],
            'summary' => $summary,
            'filtered_total' => count($rows),
            'auditors' => array_values($byAuditor),
            'rows' => $rows,
            'events' => $recentEvents,
            'auditor_options' => $auditorOptions,
            'reviewer_options' => $reviewerOptions,
            'position_options' => $positionOptions,
        ];
    }

    public function isEditableByMaker(AuditReport $report): bool
    {
        return in_array($report->status, [
            AuditReport::STATUS_DRAFT,
            AuditReport::STATUS_COMPLETED,
            AuditReport::STATUS_CHANGES_REQUESTED,
        ], true);
    }

    public function isLocked(AuditReport $report): bool
    {
        return $report->status === AuditReport::STATUS_REVIEWED
            || $report->status === AuditReport::STATUS_IN_REVIEW;
    }

    /**
     * @return list<string>
     */
    public function summarizePagesChanges(?array $before, ?array $after): array
    {
        if ($before === null) {
            return ['Full report content updated since previous review (no earlier snapshot).'];
        }

        $after = $after ?? [];
        $changes = [];

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        sort($keys);

        foreach ($keys as $key) {
            $left = $before[$key] ?? null;
            $right = $after[$key] ?? null;
            if (json_encode($left) === json_encode($right)) {
                continue;
            }

            $label = $this->humanizePagesKey((string) $key);
            if (is_array($left) && is_array($right)) {
                $leftCount = count($left);
                $rightCount = count($right);
                if ($leftCount !== $rightCount) {
                    $changes[] = $label.': '.$leftCount.' → '.$rightCount.' items';
                } else {
                    $changes[] = $label.' updated';
                }
            } elseif ($left === null) {
                $changes[] = $label.' added';
            } elseif ($right === null) {
                $changes[] = $label.' removed';
            } else {
                $changes[] = $label.' changed';
            }
        }

        if ($changes === [] && hash('sha256', json_encode($before)) !== hash('sha256', json_encode($after))) {
            $changes[] = 'Report content changed';
        }

        return array_slice($changes, 0, 20);
    }

    private function humanizePagesKey(string $key): string
    {
        $map = [
            'cover' => 'Cover page',
            'toc' => 'Table of contents',
            'findings' => 'Findings',
            'observations' => 'Observations',
            'checklist' => 'Checklist',
            'signatures' => 'Signatures',
            'page2' => 'Page 2',
            'page3' => 'Page 3',
            'page4' => 'Page 4',
            'page5' => 'Page 5 / signatures',
            'matrix' => 'Findings matrix',
            'summary' => 'Summary',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    private function composeSubmitBody(
        ?AuditReviewerAssignment $assignment,
        bool $ccSuperadmin,
        ?string $note,
        string $destination = 'assigned',
        ?User $superadmin = null,
        bool $isResubmit = false,
        int $round = 1,
    ): string {
        $parts = [];
        $parts[] = $isResubmit
            ? 'Type: Re-review after changes ('.AuditReport::reviewRoundLabel($round).')'
            : 'Type: 1st review';

        if ($destination === 'superadmin') {
            $parts[] = 'Sent to: Super Admin'.($superadmin?->name ? ' ('.$superadmin->name.')' : '');
        } else {
            $parts[] = 'Assigned reviewer: '.($assignment?->reviewer?->name ?: '#'.($assignment?->reviewer_user_id ?? '?'));
            if ($ccSuperadmin) {
                $parts[] = 'Also notified: Super Admin';
            }
        }
        if (trim((string) $note) !== '') {
            $parts[] = 'Maker note: '.trim((string) $note);
        }

        return implode("\n", $parts);
    }
}
