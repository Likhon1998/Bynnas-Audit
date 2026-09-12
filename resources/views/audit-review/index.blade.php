<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-[16px] font-semibold tracking-tight text-navy-900">Review Panel</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">What you need to do · inbox · returned · reviewed</p>
            </div>
            <div class="flex flex-wrap gap-1.5">
                @can('audits.review_assign')
                    <a href="{{ route('audit-review.assignments') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Assign reviewers</a>
                @endcan
                <a href="{{ route('audits.index') }}" class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Audit Reports</a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="mb-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($notifications as $note)
                @php
                    $tone = match ($note['tone']) {
                        'amber' => ['card' => 'border-amber-200 bg-amber-50/80', 'title' => 'text-amber-950', 'body' => 'text-amber-800/90', 'btn' => 'bg-amber-700 hover:bg-amber-800 text-white', 'badge' => 'bg-amber-600 text-white'],
                        'sky' => ['card' => 'border-sky-200 bg-sky-50/80', 'title' => 'text-sky-950', 'body' => 'text-sky-800/90', 'btn' => 'bg-[#2b579a] hover:bg-[#204072] text-white', 'badge' => 'bg-sky-600 text-white'],
                        'rose' => ['card' => 'border-rose-200 bg-rose-50/80', 'title' => 'text-rose-950', 'body' => 'text-rose-800/90', 'btn' => 'bg-rose-700 hover:bg-rose-800 text-white', 'badge' => 'bg-rose-600 text-white'],
                        default => ['card' => 'border-emerald-200 bg-emerald-50/70', 'title' => 'text-emerald-950', 'body' => 'text-emerald-800/90', 'btn' => 'bg-emerald-700 hover:bg-emerald-800 text-white', 'badge' => 'bg-emerald-600 text-white'],
                    };
                @endphp
                <div class="rounded-xl border px-3.5 py-3 shadow-sm {{ $tone['card'] }}">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[13px] font-semibold tracking-tight {{ $tone['title'] }}">{{ $note['title'] }}</p>
                        @if (($note['count'] ?? 0) > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold {{ $tone['badge'] }}">{{ $note['count'] }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-[12px] leading-relaxed {{ $tone['body'] }}">{{ $note['body'] }}</p>
                    <a href="{{ $note['url'] }}" class="mt-2.5 inline-flex h-8 items-center rounded-md px-2.5 text-[11px] font-semibold {{ $tone['btn'] }}">
                        {{ $note['action'] }}
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mb-3 flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            @foreach ([
                'inbox' => ['label' => 'Inbox', 'count' => $counts['inbox'] ?? 0],
                'returned' => ['label' => 'Returned to me', 'count' => $counts['returned'] ?? 0],
                'reviewed' => ['label' => 'Reviewed', 'count' => $counts['ready_to_send'] ?? 0],
            ] as $key => $meta)
                <a
                    href="{{ route('audit-review.index', ['tab' => $key]) }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-[12px] font-semibold {{ $tab === $key ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}"
                >
                    <span>{{ $meta['label'] }}</span>
                    @if ($meta['count'] !== null && $meta['count'] > 0)
                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[9px] font-bold {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $meta['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if ($tab === 'reviewed')
            <div class="mb-3 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-[12px] text-sky-900">
                <p class="font-semibold">Reviewed tab</p>
                <p class="mt-1"><span class="font-semibold">Reviewers:</span> <span class="font-semibold">Review ready</span> → Send to maker (for fixes) or Confirm (final lock).</p>
                <p class="mt-1"><span class="font-semibold">Makers:</span> when the reviewer sends it back, it appears under <span class="font-semibold">Returned to me</span> with <span class="font-semibold">Edit report</span>. Confirmed reports stay here (view only).</p>
            </div>
        @endif

        @if ($tab === 'returned')
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-900">
                Fix the report in the full Audit Report editor, then resubmit for review.
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-3 py-2.5">Shakha</th>
                            <th class="px-3 py-2.5">Period</th>
                            <th class="px-3 py-2.5">Maker</th>
                            <th class="px-3 py-2.5">Reviewer</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5">What to do</th>
                            <th class="px-3 py-2.5">Submitted</th>
                            <th class="px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            @php
                                $user = auth()->user();
                                $canActRow = $reviews->canActAsReviewer($user, $report);
                                $isReadyRow = $report->isInReview() && $report->isReviewReady() && $canActRow;
                                $isMakerOfRow = (int) $report->user_id === (int) $user->id;
                                $canReopen = $tab === 'reviewed'
                                    && $report->isReviewed()
                                    && (
                                        (int) $report->reviewer_user_id === (int) $user->id
                                        || $user->hasRole('superadmin')
                                        || $user->can('audits.manage')
                                    );
                                $todo = 'Open';
                                if ($tab === 'inbox' && $canActRow) {
                                    $todo = 'Review & mark comments';
                                } elseif ($tab === 'returned') {
                                    $todo = 'Edit report, then resubmit';
                                } elseif ($tab === 'reviewed') {
                                    if ($isReadyRow) {
                                        $todo = 'Send for fixes, or Confirm';
                                    } elseif ($report->isReviewed() && $isMakerOfRow && ! $canActRow) {
                                        $todo = 'Confirmed — view only';
                                    } elseif ($canReopen) {
                                        $todo = 'Confirmed — reopen if needed';
                                    } elseif ($report->isReviewed()) {
                                        $todo = 'Confirmed — view only';
                                    } else {
                                        $todo = 'Open';
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-sky-50/40">
                                <td class="px-3 py-2.5 font-medium text-slate-800">{{ $report->entityDisplayName() }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-slate-600">{{ $report->periodLabel() }}</td>
                                <td class="px-3 py-2.5 text-slate-700">{{ $report->user?->name ?: '—' }}</td>
                                <td class="px-3 py-2.5 text-slate-600">{{ $report->reviewer?->name ?: '—' }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold
                                        {{ $report->isInReview() ? ($report->isReviewReady() ? 'bg-sky-50 text-sky-800' : 'bg-amber-50 text-amber-800') : ($report->isChangesRequested() ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-800') }}">
                                        {{ $report->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-[11px] font-medium text-slate-700">{{ $todo }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-slate-500">
                                    {{ $report->submitted_for_review_at?->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?: '—' }}
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        @if ($tab === 'inbox' && $canActRow)
                                            <a href="{{ route('audit-review.show', $report) }}" class="inline-flex h-7 items-center rounded-md bg-emerald-700 px-2.5 text-[11px] font-semibold text-white hover:bg-emerald-800">Open to edit</a>
                                            <details class="relative">
                                                <summary class="cursor-pointer list-none text-[11px] font-semibold text-rose-700 hover:underline [&::-webkit-details-marker]:hidden">Return</summary>
                                                <form method="POST" action="{{ route('audit-review.request-changes', $report) }}" class="absolute right-0 z-20 mt-1 w-56 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                                                    @csrf
                                                    <textarea name="body" rows="3" required class="mb-2 w-full rounded-md border-slate-200 text-[11px]" placeholder="What should the maker fix?"></textarea>
                                                    <button type="submit" class="inline-flex h-8 w-full items-center justify-center rounded-md bg-rose-700 text-[11px] font-semibold text-white hover:bg-rose-800">Return to maker</button>
                                                </form>
                                            </details>
                                        @elseif ($tab === 'returned')
                                            <a href="{{ route('audits.index', ['report' => $report->id]) }}" class="inline-flex h-7 items-center rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white hover:bg-[#204072]">Edit report</a>
                                            <a href="{{ route('audit-review.show', $report) }}" class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">View comments</a>
                                        @elseif ($isReadyRow)
                                            <a href="{{ route('audit-review.show', $report) }}" class="inline-flex h-7 items-center rounded-md bg-emerald-700 px-2.5 text-[11px] font-semibold text-white hover:bg-emerald-800">Edit marks</a>
                                            <form
                                                method="POST"
                                                action="{{ route('audit-review.send-to-maker', $report) }}"
                                                class="inline"
                                                data-bynnas-confirm="{{ $report->user?->name ?: 'the maker' }} will fix based on your marks/comments, then resubmit for review. This does not lock the report."
                                                data-bynnas-confirm-title="Send to {{ $report->user?->name ?: 'the maker' }} for fixes?"
                                                data-bynnas-confirm-ok="Send for fixes"
                                                data-bynnas-confirm-tone="sky"
                                            >
                                                @csrf
                                                <button type="submit" class="inline-flex h-7 items-center rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white hover:bg-[#204072]">Send to maker</button>
                                            </form>
                                            <form
                                                method="POST"
                                                action="{{ route('audit-review.approve', $report) }}"
                                                class="inline"
                                                data-bynnas-confirm="This locks the report as Confirmed. The maker cannot edit it further."
                                                data-bynnas-confirm-title="Confirm this report?"
                                                data-bynnas-confirm-ok="Confirm"
                                                data-bynnas-confirm-tone="emerald"
                                            >
                                                @csrf
                                                <button type="submit" class="inline-flex h-7 items-center rounded-md bg-emerald-700 px-2.5 text-[11px] font-semibold text-white hover:bg-emerald-800">Confirm</button>
                                            </form>
                                        @elseif ($canReopen)
                                            <form
                                                method="POST"
                                                action="{{ route('audit-review.reopen', $report) }}"
                                                class="inline"
                                                data-bynnas-confirm="You can edit marks again, then Send to maker or Confirm."
                                                data-bynnas-confirm-title="Reopen this confirmed report?"
                                                data-bynnas-confirm-ok="Reopen"
                                                data-bynnas-confirm-tone="amber"
                                            >
                                                @csrf
                                                <button type="submit" class="inline-flex h-7 items-center rounded-md bg-amber-600 px-2.5 text-[11px] font-semibold text-white hover:bg-amber-700">Reopen</button>
                                            </form>
                                            <a href="{{ route('audit-review.show', $report) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Open</a>
                                        @else
                                            <a href="{{ route('audit-review.show', $report) }}" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Open</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-12 text-center text-slate-400">No reports in this tab.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($reports, 'links'))
                <div class="border-t border-slate-100 px-3 py-2">{{ $reports->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
