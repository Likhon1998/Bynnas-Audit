<?php

namespace App\Http\Controllers;

use App\Models\AuditReport;
use App\Models\AuditReviewerAssignment;
use App\Models\User;
use App\Services\AuditReportReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditReportReviewController extends Controller
{
    public function index(Request $request, AuditReportReviewService $reviews): View
    {
        $user = $request->user();
        $tab = (string) $request->input('tab', 'inbox');
        if (! in_array($tab, ['inbox', 'returned', 'reviewed'], true)) {
            $tab = 'inbox';
        }

        $reports = collect();

        if ($tab === 'inbox') {
            $query = AuditReport::query()
                ->with(['shakha:id,name,code', 'projectLocation.project', 'user:id,name', 'reviewer:id,name'])
                ->where('status', AuditReport::STATUS_IN_REVIEW)
                ->whereNull('review_ready_at')
                ->orderByDesc('submitted_for_review_at')
                ->orderByDesc('id');

            if ($user->hasRole('superadmin')) {
                // Superadmin sees all pending in-review.
            } else {
                $query->where('reviewer_user_id', $user->id);
            }

            $reports = $query->paginate(30)->withQueryString();
        } elseif ($tab === 'returned') {
            $reports = AuditReport::query()
                ->with(['shakha:id,name,code', 'projectLocation.project', 'user:id,name', 'reviewer:id,name'])
                ->where('status', AuditReport::STATUS_CHANGES_REQUESTED)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('collaborators', fn ($c) => $c->where('users.id', $user->id));
                })
                ->orderByDesc('updated_at')
                ->paginate(30)
                ->withQueryString();
        } else {
            // Reviewed tab:
            // - Reviewers: Review ready (send/confirm) + Confirmed
            // - Makers: Confirmed only (ready items stay with the reviewer until Send)
            $query = AuditReport::query()
                ->with(['shakha:id,name,code', 'projectLocation.project', 'user:id,name', 'reviewer:id,name'])
                ->where(function ($q) use ($user) {
                    $q->where(function ($confirmed) use ($user) {
                        $confirmed->where('status', AuditReport::STATUS_REVIEWED)
                            ->where(function ($own) use ($user) {
                                $own->where('user_id', $user->id)
                                    ->orWhere('reviewer_user_id', $user->id)
                                    ->orWhereHas('collaborators', fn ($c) => $c->where('users.id', $user->id));
                            });
                    });

                    // Ready-to-send is reviewer work only.
                    if ($user->can('audits.review') || $user->hasRole('superadmin') || $user->isSuperAdmin()) {
                        $q->orWhere(function ($ready) use ($user) {
                            $ready->where('status', AuditReport::STATUS_IN_REVIEW)
                                ->whereNotNull('review_ready_at');

                            if (! $user->hasRole('superadmin') && ! $user->isSuperAdmin()) {
                                $ready->where('reviewer_user_id', $user->id);
                            }
                        });
                    }
                })
                ->orderByRaw("CASE WHEN status = 'in_review' THEN 0 ELSE 1 END")
                ->orderByDesc('review_ready_at')
                ->orderByDesc('reviewed_at')
                ->orderByDesc('id');

            $reports = $query->paginate(30)->withQueryString();
        }

        $counts = $reviews->actionCounts($user);
        $notifications = $reviews->actionNotifications($user);

        return view('audit-review.index', [
            'tab' => $tab,
            'reports' => $reports,
            'reviews' => $reviews,
            'counts' => $counts,
            'notifications' => $notifications,
        ]);
    }

    public function show(AuditReport $report, AuditReportReviewService $reviews): View
    {
        $user = auth()->user();
        abort_unless(
            $reviews->canReview($user, $report)
                || $report->isAccessibleBy($user)
                || $user->can('audits.review_assign'),
            403
        );

        $report->load([
            'shakha:id,name,code',
            'projectLocation.project',
            'user:id,name,email',
            'reviewer:id,name,email',
            'reviewEvents.actor:id,name',
            'reviewAnnotations.user:id,name',
        ]);

        $preview = [];
        $previewError = null;
        try {
            $preview = \App\Livewire\MakeAuditReport::viewDataFor($report);
            $preview['logoUrl'] = $preview['logoDataUri'] ?? null;
        } catch (\Throwable $e) {
            report($e);
            $previewError = 'Could not render this report for on-screen review.';
        }

        return view('audit-review.show', [
            'report' => $report,
            'canAct' => $reviews->canActAsReviewer($user, $report),
            'canAnnotate' => $reviews->canActAsReviewer($user, $report),
            'reviewReady' => $report->isReviewReady(),
            'canSendToMaker' => $reviews->canActAsReviewer($user, $report) && $report->isReviewReady(),
            'canConfirm' => $reviews->canActAsReviewer($user, $report) && $report->isReviewReady(),
            'canDownloadReviewPack' => $reviews->canReview($user, $report)
                || $report->isAccessibleBy($user)
                || $user->can('audits.review_assign')
                || $user->can('audits.manage'),
            'documentUrl' => route('audit-review.document', $report),
            'downloadReviewUrl' => route('audit-review.download', $report),
            'interactiveUrl' => route('audits.index', [
                'report' => $report->id,
                'mode' => 'review',
                'preview' => 1,
            ]),
            'preview' => $preview,
            'previewError' => $previewError,
            'annotations' => $report->reviewAnnotations
                ->map(fn (\App\Models\AuditReportReviewAnnotation $a) => $a->toReviewPayload())
                ->values()
                ->all(),
            'storeAnnotationUrl' => route('audit-review.annotations.store', $report),
        ]);
    }

    public function document(AuditReport $report, AuditReportReviewService $reviews): \Symfony\Component\HttpFoundation\Response
    {
        $user = auth()->user();
        abort_unless(
            $reviews->canReview($user, $report)
                || $report->isAccessibleBy($user)
                || $user->can('audits.review_assign')
                || $user->can('audits.manage'),
            403
        );

        try {
            $binary = \App\Livewire\MakeAuditReport::pdfBinaryFor($report);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Could not render this report for review.');
        }

        $name = 'audit-report-'.$report->id.'.pdf';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$name.'"',
            'Cache-Control' => 'private, max-age=60',
        ]);
    }

    public function submit(Request $request, AuditReport $report, AuditReportReviewService $reviews): RedirectResponse
    {
        $data = $request->validate([
            'destination' => ['nullable', 'in:assigned,superadmin'],
            'cc_superadmin' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $destination = (string) ($data['destination'] ?? 'assigned');
        $cc = (bool) ($data['cc_superadmin'] ?? false);
        if ($destination === 'superadmin') {
            $cc = true;
        }

        $reviews->submitForReview(
            $report,
            $request->user(),
            $cc,
            $data['note'] ?? null,
            $destination,
        );

        $label = $destination === 'superadmin' ? 'Super Admin' : 'your reviewer';

        return redirect()
            ->route('audit-review.show', $report)
            ->with('status', 'Report sent for review to '.$label.'.');
    }

    public function requestChanges(Request $request, AuditReport $report, AuditReportReviewService $reviews): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $reviews->requestChanges($report, $request->user(), $data['body']);

        return redirect()
            ->route('audit-review.index', ['tab' => 'inbox'])
            ->with('status', 'Changes requested — returned to the report maker.');
    }

    public function approve(Request $request, AuditReport $report, AuditReportReviewService $reviews): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $reviews->approve($report, $request->user(), $data['note'] ?? null);

        return redirect()
            ->route('audit-review.index', ['tab' => 'reviewed'])
            ->with('status', 'Report confirmed and locked.');
    }

    public function completeReview(Request $request, AuditReport $report, AuditReportReviewService $reviews): RedirectResponse
    {
        abort_unless($reviews->canActAsReviewer($request->user(), $report), 403);

        if (! $report->review_ready_at) {
            $report->update(['review_ready_at' => now()]);
            \App\Models\AuditReportReviewEvent::query()->create([
                'audit_report_id' => $report->id,
                'actor_user_id' => $request->user()->id,
                'action' => \App\Models\AuditReportReviewEvent::ACTION_NOTE,
                'body' => 'Review marked done. Send to maker for fixes, or Confirm to lock finally.',
            ]);
        }

        return redirect()
            ->route('audit-review.index', ['tab' => 'reviewed'])
            ->with('status', 'Review done. Under Reviewed: Send to maker (they fix & resubmit) or Confirm (final).');
    }

    public function sendToMaker(Request $request, AuditReport $report, AuditReportReviewService $reviews): RedirectResponse
    {
        $report->load(['user:id,name']);
        $maker = $report->user;

        $reviews->sendToMaker($report, $request->user());

        return redirect()
            ->route('audit-review.index', ['tab' => 'reviewed'])
            ->with(
                'status',
                'Sent to '.($maker?->name ?: 'the maker').' for fixes. They will see your marks/comments, fix the report, and resubmit. Then you can Confirm.'
            );
    }

    public function reopenReview(Request $request, AuditReport $report, AuditReportReviewService $reviews): RedirectResponse
    {
        $user = $request->user();
        abort_unless($report->isReviewed(), 422, 'Only confirmed reviews can be reopened.');
        abort_unless(
            (int) $report->reviewer_user_id === (int) $user->id
                || $user->hasRole('superadmin')
                || $user->can('audits.manage'),
            403
        );

        $report->update([
            'status' => AuditReport::STATUS_IN_REVIEW,
            'reviewed_at' => null,
            'review_ready_at' => now(),
            'review_sent_to_maker_at' => null,
        ]);

        \App\Models\AuditReportReviewEvent::query()->create([
            'audit_report_id' => $report->id,
            'actor_user_id' => $user->id,
            'action' => \App\Models\AuditReportReviewEvent::ACTION_NOTE,
            'body' => 'Confirmed review reopened. Send to maker for more fixes, or Confirm again when ready.',
        ]);

        return redirect()
            ->route('audit-review.show', $report)
            ->with('status', 'Reopened. You can edit marks, Send to maker, or Confirm.');
    }

    public function downloadReviewPack(AuditReport $report, AuditReportReviewService $reviews): \Symfony\Component\HttpFoundation\Response
    {
        $user = auth()->user();
        abort_unless(
            $reviews->canReview($user, $report)
                || $report->isAccessibleBy($user)
                || $user->can('audits.review_assign')
                || $user->can('audits.manage'),
            403
        );

        $report->load(['reviewAnnotations.user:id,name']);

        try {
            $reportPdf = \App\Livewire\MakeAuditReport::pdfBinaryFor($report);
            $commentsPdf = app(\App\Services\AuditReportReviewCommentsPdfService::class)
                ->output($report, $report->reviewAnnotations);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Could not build the review download.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'review-pack-');
        if ($zipPath === false) {
            abort(500, 'Could not create download file.');
        }
        @unlink($zipPath);
        $zipPath .= '.zip';

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create zip download.');
        }
        $zip->addFromString('audit-report-'.$report->id.'.pdf', $reportPdf);
        $zip->addFromString('review-comments-'.$report->id.'.pdf', $commentsPdf);
        $zip->close();

        return response()->download(
            $zipPath,
            'review-pack-'.$report->id.'.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function storeAnnotation(Request $request, AuditReport $report, AuditReportReviewService $reviews): \Illuminate\Http\JsonResponse
    {
        abort_unless($reviews->canActAsReviewer($request->user(), $report), 403);

        $data = $request->validate([
            'type' => ['nullable', 'in:text,area'],
            'quote' => ['nullable', 'string', 'max:2000'],
            'prefix' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'color' => ['required', 'in:'.implode(',', \App\Models\AuditReportReviewAnnotation::COLORS)],
            'rect_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rect_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rect_w' => ['nullable', 'numeric', 'min:0.2', 'max:100'],
            'rect_h' => ['nullable', 'numeric', 'min:0.2', 'max:100'],
            'snapshot' => ['nullable', 'string', 'max:900000'],
        ]);

        $type = (string) ($data['type'] ?? \App\Models\AuditReportReviewAnnotation::TYPE_TEXT);
        if ($type === \App\Models\AuditReportReviewAnnotation::TYPE_AREA) {
            foreach (['rect_x', 'rect_y', 'rect_w', 'rect_h'] as $key) {
                if (! isset($data[$key])) {
                    return response()->json(['message' => 'Area bounds are required.'], 422);
                }
            }
            if (((float) $data['rect_x'] + (float) $data['rect_w']) > 100.01
                || ((float) $data['rect_y'] + (float) $data['rect_h']) > 100.01) {
                return response()->json(['message' => 'Area is outside the document.'], 422);
            }
            $quote = filled($data['quote'] ?? null) ? trim((string) $data['quote']) : 'Marked area';
        } else {
            $quote = trim((string) ($data['quote'] ?? ''));
            if ($quote === '') {
                return response()->json(['message' => 'Selected text is required.'], 422);
            }
        }

        $snapshotPath = $this->storeAnnotationSnapshot(
            $report,
            (string) ($data['snapshot'] ?? '')
        );

        $annotation = $report->reviewAnnotations()->create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'color' => $data['color'],
            'quote' => $quote,
            'prefix' => $type === \App\Models\AuditReportReviewAnnotation::TYPE_TEXT && isset($data['prefix'])
                ? mb_substr(trim((string) $data['prefix']), 0, 255)
                : null,
            'suffix' => $type === \App\Models\AuditReportReviewAnnotation::TYPE_TEXT && isset($data['suffix'])
                ? mb_substr(trim((string) $data['suffix']), 0, 255)
                : null,
            'body' => filled($data['body'] ?? null) ? trim((string) $data['body']) : null,
            'rect_x' => $type === \App\Models\AuditReportReviewAnnotation::TYPE_AREA ? round((float) $data['rect_x'], 4) : null,
            'rect_y' => $type === \App\Models\AuditReportReviewAnnotation::TYPE_AREA ? round((float) $data['rect_y'], 4) : null,
            'rect_w' => $type === \App\Models\AuditReportReviewAnnotation::TYPE_AREA ? round((float) $data['rect_w'], 4) : null,
            'rect_h' => $type === \App\Models\AuditReportReviewAnnotation::TYPE_AREA ? round((float) $data['rect_h'], 4) : null,
            'snapshot_path' => $snapshotPath,
        ]);
        $annotation->load('user:id,name');

        return response()->json([
            'annotation' => $annotation->toReviewPayload(),
        ], 201);
    }

    private function storeAnnotationSnapshot(AuditReport $report, string $dataUrl): ?string
    {
        $dataUrl = trim($dataUrl);
        if ($dataUrl === '' || ! preg_match('#^data:image/(jpeg|jpg|png|webp);base64,#i', $dataUrl, $m)) {
            return null;
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($binary === false || strlen($binary) < 200 || strlen($binary) > 650000) {
            return null;
        }

        $ext = strtolower($m[1]) === 'jpg' ? 'jpg' : strtolower($m[1]);
        $path = 'review-snapshots/'.$report->id.'/'.uniqid('mark_', true).'.'.$ext;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary);

        return $path;
    }

    public function updateAnnotation(
        Request $request,
        AuditReport $report,
        \App\Models\AuditReportReviewAnnotation $annotation,
        AuditReportReviewService $reviews,
    ): \Illuminate\Http\JsonResponse {
        abort_unless($annotation->audit_report_id === $report->id, 404);
        abort_unless(
            $annotation->user_id === $request->user()->id
                || $reviews->canActAsReviewer($request->user(), $report)
                || $request->user()->can('audits.manage'),
            403
        );
        abort_unless($reviews->canActAsReviewer($request->user(), $report) || $request->user()->can('audits.manage'), 403);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'in:'.implode(',', \App\Models\AuditReportReviewAnnotation::COLORS)],
            'rect_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rect_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rect_w' => ['nullable', 'numeric', 'min:0.2', 'max:100'],
            'rect_h' => ['nullable', 'numeric', 'min:0.2', 'max:100'],
            'snapshot' => ['nullable', 'string', 'max:900000'],
        ]);

        if ($annotation->isArea() && array_key_exists('body', $data) && ! filled($data['body'])) {
            return response()->json(['message' => 'Area notes cannot be empty.'], 422);
        }

        if (array_key_exists('body', $data)) {
            $annotation->body = filled($data['body']) ? trim((string) $data['body']) : null;
        }
        if (isset($data['color'])) {
            $annotation->color = $data['color'];
        }
        if ($annotation->isArea()) {
            foreach (['rect_x', 'rect_y', 'rect_w', 'rect_h'] as $key) {
                if (isset($data[$key])) {
                    $annotation->{$key} = round((float) $data[$key], 4);
                }
            }
            if ($annotation->rect_x !== null && $annotation->rect_w !== null
                && ((float) $annotation->rect_x + (float) $annotation->rect_w) > 100.01) {
                return response()->json(['message' => 'Area is outside the document.'], 422);
            }
            if ($annotation->rect_y !== null && $annotation->rect_h !== null
                && ((float) $annotation->rect_y + (float) $annotation->rect_h) > 100.01) {
                return response()->json(['message' => 'Area is outside the document.'], 422);
            }
        }

        if (! empty($data['snapshot'])) {
            $path = $this->storeAnnotationSnapshot($report, (string) $data['snapshot']);
            if ($path) {
                if ($annotation->snapshot_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($annotation->snapshot_path);
                }
                $annotation->snapshot_path = $path;
            }
        }

        $annotation->save();
        $annotation->load('user:id,name');

        return response()->json([
            'annotation' => $annotation->toReviewPayload(),
        ]);
    }

    public function attachAnnotationSnapshot(
        Request $request,
        AuditReport $report,
        \App\Models\AuditReportReviewAnnotation $annotation,
        AuditReportReviewService $reviews,
    ): \Illuminate\Http\JsonResponse {
        abort_unless($annotation->audit_report_id === $report->id, 404);
        abort_unless(
            $reviews->canReview($request->user(), $report)
                || $report->isAccessibleBy($request->user())
                || $request->user()->can('audits.manage'),
            403
        );

        $data = $request->validate([
            'snapshot' => ['required', 'string', 'max:900000'],
        ]);

        $path = $this->storeAnnotationSnapshot($report, (string) $data['snapshot']);
        if (! $path) {
            return response()->json(['message' => 'Could not save snapshot image.'], 422);
        }

        if ($annotation->snapshot_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($annotation->snapshot_path);
        }

        $annotation->snapshot_path = $path;
        $annotation->save();
        $annotation->load('user:id,name');

        return response()->json([
            'annotation' => $annotation->toReviewPayload(),
        ]);
    }

    public function destroyAnnotation(
        Request $request,
        AuditReport $report,
        \App\Models\AuditReportReviewAnnotation $annotation,
        AuditReportReviewService $reviews,
    ): \Illuminate\Http\JsonResponse {
        abort_unless($annotation->audit_report_id === $report->id, 404);
        abort_unless(
            $annotation->user_id === $request->user()->id
                || $reviews->canActAsReviewer($request->user(), $report)
                || $request->user()->can('audits.manage'),
            403
        );

        if ($annotation->snapshot_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($annotation->snapshot_path);
        }

        $annotation->delete();

        return response()->json(['ok' => true]);
    }

    public function assignments(): View
    {
        $auditors = User::query()
            ->permission('audits.create')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $reviewers = User::query()
            ->permission('audits.review')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $map = AuditReviewerAssignment::query()
            ->get()
            ->keyBy('auditor_user_id');

        return view('audit-review.assignments', [
            'auditors' => $auditors,
            'reviewers' => $reviewers,
            'map' => $map,
        ]);
    }

    public function saveAssignments(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'assignments' => ['nullable', 'array'],
            'assignments.*.auditor_user_id' => ['required', 'integer', 'exists:users,id'],
            'assignments.*.reviewer_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $rows = $data['assignments'] ?? [];
        $actorId = $request->user()->id;

        foreach ($rows as $row) {
            $auditorId = (int) $row['auditor_user_id'];
            $reviewerId = (int) ($row['reviewer_user_id'] ?? 0);

            if ($reviewerId < 1) {
                AuditReviewerAssignment::query()->where('auditor_user_id', $auditorId)->delete();
                continue;
            }

            if ($reviewerId === $auditorId) {
                continue;
            }

            AuditReviewerAssignment::query()->updateOrCreate(
                ['auditor_user_id' => $auditorId],
                [
                    'reviewer_user_id' => $reviewerId,
                    'assigned_by' => $actorId,
                ]
            );
        }

        return redirect()
            ->route('audit-review.assignments')
            ->with('status', 'Reviewer assignments saved.');
    }
}
