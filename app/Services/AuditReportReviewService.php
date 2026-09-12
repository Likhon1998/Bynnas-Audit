<?php

namespace App\Services;

use App\Models\AuditReport;
use App\Models\AuditReportReviewEvent;
use App\Models\AuditReviewerAssignment;
use App\Models\User;
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

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if (! $user->can('audits.review')) {
            return false;
        }

        if ((int) $report->reviewer_user_id === (int) $user->id) {
            return true;
        }

        return (bool) $report->review_cc_superadmin && $user->hasRole('superadmin');
    }

    public function canActAsReviewer(User $user, AuditReport $report): bool
    {
        if ($report->status !== AuditReport::STATUS_IN_REVIEW) {
            return false;
        }

        if ((int) $report->reviewer_user_id === (int) $user->id) {
            return true;
        }

        return (bool) $report->review_cc_superadmin
            && ($user->hasRole('superadmin') || $user->isSuperAdmin());
    }

    /**
     * Counts of actions the user should take in the Review Panel.
     *
     * @return array{inbox:int,ready_to_send:int,returned:int,sent_to_me:int,total:int}
     */
    public function actionCounts(User $user): array
    {
        $inbox = 0;
        $ready = 0;
        $returned = 0;
        $sentToMe = 0;

        if ($user->can('audits.review') || $user->hasRole('superadmin')) {
            $inboxQuery = AuditReport::query()
                ->where('status', AuditReport::STATUS_IN_REVIEW)
                ->whereNull('review_ready_at');
            $readyQuery = AuditReport::query()
                ->where('status', AuditReport::STATUS_IN_REVIEW)
                ->whereNotNull('review_ready_at')
                ->whereNull('review_sent_to_maker_at');

            if (! $user->hasRole('superadmin') && ! $user->isSuperAdmin()) {
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
     * Human action cards for the Review Panel.
     *
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
                'body' => 'Open each report, mark issues, click Review done, then Send to maker (for fixes) or Confirm (final).',
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
                'body' => 'Send to maker = they fix & resubmit. Confirm = final lock (no more changes).',
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
                'body' => 'Open Edit report to change the full audit like a normal draft. View comments for marks, then resubmit for review.',
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
     * @throws ValidationException
     */
    public function submitForReview(
        AuditReport $report,
        User $actor,
        bool $ccSuperadmin = false,
        ?string $note = null,
        string $destination = 'assigned',
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

        return DB::transaction(function () use ($report, $actor, $assignment, $ccSuperadmin, $note, $isResubmit, $reviewerUserId, $destination, $superadmin) {
            $report->update([
                'status' => AuditReport::STATUS_IN_REVIEW,
                'reviewer_user_id' => $reviewerUserId,
                'submitted_for_review_at' => now(),
                'review_round' => (int) $report->review_round + 1,
                'review_cc_superadmin' => $ccSuperadmin,
                'progress_pct' => max((int) $report->progress_pct, 100),
                'completed_at' => $report->completed_at ?? now(),
                // Fresh review round — clear prior ready/send/confirm stamps.
                'review_ready_at' => null,
                'review_sent_to_maker_at' => null,
                'reviewed_at' => null,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => $isResubmit
                    ? AuditReportReviewEvent::ACTION_RESUBMITTED
                    : AuditReportReviewEvent::ACTION_SUBMITTED,
                'body' => $this->composeSubmitBody($assignment, $ccSuperadmin, $note, $destination, $superadmin),
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

        return DB::transaction(function () use ($report, $actor, $comment) {
            $report->update([
                'status' => AuditReport::STATUS_CHANGES_REQUESTED,
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_RETURNED,
                'body' => $comment,
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
    }

    /**
     * Send review marks back to the maker so they can fix and resubmit.
     * Does NOT lock the report — final lock is approve()/Confirm.
     *
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

        $markCount = $report->reviewAnnotations()->count();
        $makerName = $report->user?->name ?: 'the maker';

        return DB::transaction(function () use ($report, $actor, $markCount, $makerName) {
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
                'body' => 'Sent to '.$makerName.' with review marks/comments ('.$markCount.'). '
                    .'Maker must fix and resubmit; then the reviewer can confirm.',
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

        return DB::transaction(function () use ($report, $actor, $note) {
            $report->update([
                'status' => AuditReport::STATUS_REVIEWED,
                'reviewed_at' => now(),
                'review_ready_at' => $report->review_ready_at ?? now(),
            ]);

            AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $actor->id,
                'action' => AuditReportReviewEvent::ACTION_APPROVED,
                'body' => trim((string) $note) !== ''
                    ? trim((string) $note)
                    : 'Confirmed — report locked as reviewed.',
            ]);

            return $report->fresh(['reviewer', 'user', 'shakha']);
        });
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

    private function composeSubmitBody(
        ?AuditReviewerAssignment $assignment,
        bool $ccSuperadmin,
        ?string $note,
        string $destination = 'assigned',
        ?User $superadmin = null,
    ): string {
        $parts = [];
        if ($destination === 'superadmin') {
            $parts[] = 'Sent to: Super Admin'.($superadmin?->name ? ' ('.$superadmin->name.')' : '');
        } else {
            $parts[] = 'Assigned reviewer: '.($assignment?->reviewer?->name ?: '#'.($assignment?->reviewer_user_id ?? '?'));
            if ($ccSuperadmin) {
                $parts[] = 'Also notified: Super Admin';
            }
        }
        if (trim((string) $note) !== '') {
            $parts[] = trim((string) $note);
        }

        return implode("\n", $parts);
    }
}
