<x-app-layout>
    @php
        $btn = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3 text-[12px] font-semibold text-white transition hover:-translate-y-0.5 disabled:opacity-60';
        $tones = [
            'navy' => 'bg-[#2b579a] hover:bg-[#204072] shadow-[0_6px_14px_rgba(43,87,154,0.35)]',
            'emerald' => 'bg-emerald-600 hover:bg-emerald-700 shadow-[0_6px_14px_rgba(5,150,105,0.35)]',
            'teal' => 'bg-teal-600 hover:bg-teal-700 shadow-[0_6px_14px_rgba(13,148,136,0.35)]',
            'rose' => 'bg-rose-600 hover:bg-rose-700 shadow-[0_6px_14px_rgba(225,29,72,0.35)]',
            'amber' => 'bg-amber-500 hover:bg-amber-600 shadow-[0_6px_14px_rgba(217,119,6,0.35)]',
            'violet' => 'bg-violet-600 hover:bg-violet-700 shadow-[0_6px_14px_rgba(124,58,237,0.35)]',
            'sky' => 'bg-sky-600 hover:bg-sky-700 shadow-[0_6px_14px_rgba(2,132,199,0.35)]',
        ];
        $pill = [
            'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'teal' => 'bg-teal-50 text-teal-700 ring-teal-200',
            'amber' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'rose' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'sky' => 'bg-sky-50 text-sky-800 ring-sky-200',
            'violet' => 'bg-violet-50 text-violet-700 ring-violet-200',
        ];
        $dot = [
            'emerald' => 'bg-emerald-500',
            'teal' => 'bg-teal-500',
            'amber' => 'bg-amber-500',
            'rose' => 'bg-rose-500',
            'sky' => 'bg-sky-500',
            'violet' => 'bg-violet-500',
        ];
        $btnSm = str_replace(['h-8', 'px-3'], ['h-7', 'px-2.5'], $btn);
        $thisYear = (int) now('Asia/Dhaka')->year;

        $tabList = [
            'inbox' => ['label' => 'Inbox', 'count' => $counts['inbox'] ?? 0],
            'returned' => ['label' => 'Returned to me', 'count' => $counts['returned'] ?? 0],
            'reviewed' => ['label' => 'Reviewed', 'count' => $counts['ready_to_send'] ?? 0],
        ];
        if ($isReviewer) {
            $tabList['history'] = ['label' => 'Review history', 'count' => null];
        }

        $actionNotes = collect($notifications)->reject(fn ($n) => ($n['key'] ?? '') === 'clear')->values();
        $clearNote = collect($notifications)->firstWhere('key', 'clear');
    @endphp

    <div class="space-y-3 px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        {{-- Header: title, tabs and actions in one compact bar --}}
        <header class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#0f2147] via-[#1b3a70] to-[#2b579a] text-white shadow-[0_14px_32px_rgba(15,33,71,0.25)]">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 pt-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-[16px] font-semibold leading-tight tracking-tight">Review Panel</h1>
                        <p class="truncate text-[11px] text-slate-200/80">Review, return and confirm audit reports</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @if (auth()->user()?->canAssignReviewers())
                        <a href="{{ route('audit-review.log') }}" class="{{ $btnSm }} {{ $tones['sky'] }}">Auditors log</a>
                        <a href="{{ route('audit-review.assignments') }}" class="{{ $btnSm }} {{ $tones['violet'] }}">Assign reviewers</a>
                    @endif
                    @canany(['audits.create', 'audits.manage'])
                        <a href="{{ route('audits.index') }}" class="inline-flex h-7 items-center rounded-lg bg-white px-3 text-[12px] font-semibold text-[#1b3a70] shadow-[0_6px_14px_rgba(0,0,0,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-50">Audit Reports</a>
                    @endcanany
                </div>
            </div>
            <nav class="mt-2.5 flex gap-1 overflow-x-auto px-3">
                @foreach ($tabList as $key => $meta)
                    <a
                        href="{{ route('audit-review.index', ['tab' => $key]) }}"
                        class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-t-lg px-3.5 text-[12px] font-semibold transition {{ $tab === $key ? 'bg-slate-50 text-[#1b3a70]' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}"
                    >
                        {{ $meta['label'] }}
                        @if ($meta['count'])
                            <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $meta['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </header>

        @if (session('status'))
            <div data-flash class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-[12px] font-medium text-emerald-800">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ session('status') }}
            </div>
        @endif

        <div class="grid items-start gap-3 {{ $monthlyStats || $actionNotes->isNotEmpty() ? 'xl:grid-cols-[minmax(0,1fr)_280px]' : '' }}">
            {{-- Main work area --}}
            <main class="min-w-0 space-y-3">
                @if ($tab === 'history' && $history)
                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[14px] font-semibold text-navy-900">
                                Reviews completed in {{ $history['year'] }}
                                @if ($history['can_pick'])
                                    <span class="font-medium text-slate-500">· {{ $history['reviewer_id'] === 0 ? 'all reviewers' : (collect($history['reviewers'])->firstWhere('id', $history['reviewer_id'])['name'] ?? 'reviewer') }}</span>
                                @endif
                            </p>
                            <form method="GET" action="{{ route('audit-review.index') }}" class="flex flex-wrap items-center gap-1.5">
                                <input type="hidden" name="tab" value="history">
                                @if ($history['can_pick'])
                                    <select name="reviewer" class="h-8 min-w-[10rem] rounded-lg border-slate-200 bg-white py-1 pl-2.5 pr-8 text-[12px] text-navy-900">
                                        <option value="0" @selected($history['reviewer_id'] === 0)>All reviewers</option>
                                        @foreach ($history['reviewers'] as $option)
                                            <option value="{{ $option['id'] }}" @selected($history['reviewer_id'] === $option['id'])>{{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <select name="year" class="h-8 min-w-[5.25rem] rounded-lg border-slate-200 bg-white py-1 pl-2.5 pr-8 text-[12px] text-navy-900">
                                    @for ($y = $thisYear - 3; $y <= $thisYear; $y++)
                                        <option value="{{ $y }}" @selected($history['year'] === $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                                <button type="submit" class="{{ $btn }} {{ $tones['navy'] }}">Show</button>
                            </form>
                        </div>

                        <div class="mt-3 grid gap-3 lg:grid-cols-[220px_minmax(0,1fr)]">
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ([
                                    ['label' => 'Reviews', 'value' => $history['total'], 'tone' => 'violet'],
                                    ['label' => 'Branches', 'value' => $history['shakha_count'], 'tone' => 'sky'],
                                    ['label' => 'Confirmed', 'value' => $history['confirmed'], 'tone' => 'emerald'],
                                    ['label' => 'Sent back', 'value' => $history['returned'], 'tone' => 'amber'],
                                ] as $kpi)
                                    <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2">
                                        <span class="absolute inset-y-0 left-0 w-1 {{ $dot[$kpi['tone']] }}"></span>
                                        <p class="text-[20px] font-semibold leading-none tabular-nums text-navy-900">{{ $kpi['value'] }}</p>
                                        <p class="mt-1 text-[11px] font-medium text-slate-500">{{ $kpi['label'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-12 items-end gap-1.5 rounded-xl border border-slate-100 bg-slate-50/60 px-3 pb-2 pt-3">
                                @foreach (collect($history['months'])->sortKeys() as $m => $month)
                                    @php $height = $month['count'] > 0 ? max(10, (int) round($month['count'] / $history['peak'] * 100)) : 3; @endphp
                                    <a
                                        href="{{ $month['count'] > 0 ? '#review-month-'.$m : '#' }}"
                                        title="{{ $month['label'] }}: {{ $month['count'] }} review(s), {{ $month['shakhas'] }} branch(es)"
                                        class="group flex flex-col items-center gap-1 {{ $month['count'] > 0 ? '' : 'pointer-events-none' }}"
                                    >
                                        <span class="text-[11px] font-semibold tabular-nums {{ $month['count'] > 0 ? 'text-navy-900' : 'text-slate-300' }}">{{ $month['count'] }}</span>
                                        <span class="flex h-16 w-full items-end justify-center">
                                            <span
                                                class="w-full max-w-[26px] rounded-t-md transition group-hover:-translate-y-0.5 {{ $month['count'] > 0 ? 'bg-gradient-to-t from-[#1b3a70] to-[#4a7fd0] shadow-[0_6px_14px_rgba(43,87,154,0.3)]' : 'bg-slate-200' }}"
                                                style="height: {{ $height }}%"
                                            ></span>
                                        </span>
                                        <span class="text-[10px] font-medium uppercase text-slate-500">{{ $month['short'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    @php $activeMonths = collect($history['months'])->filter(fn ($m) => $m['count'] > 0); @endphp
                    @forelse ($activeMonths as $m => $month)
                        <section id="review-month-{{ $m }}" class="scroll-mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-2">
                                <p class="text-[13px] font-semibold text-navy-900">{{ $month['label'] }}</p>
                                <div class="flex items-center gap-1.5 text-[11px] font-semibold">
                                    <span class="rounded-full bg-[#1b3a70] px-2 py-0.5 text-white">{{ $month['count'] }} {{ $month['count'] === 1 ? 'review' : 'reviews' }}</span>
                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-sky-800">{{ $month['shakhas'] }} {{ $month['shakhas'] === 1 ? 'branch' : 'branches' }}</span>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-[12px]">
                                    <thead class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        <tr>
                                            <th class="px-4 py-2">Branch</th>
                                            <th class="px-4 py-2">Maker{{ $history['reviewer_id'] === 0 ? ' / Reviewer' : '' }}</th>
                                            <th class="px-4 py-2">Outcome</th>
                                            <th class="px-4 py-2">Reviewed on</th>
                                            <th class="px-4 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($month['rows'] as $row)
                                            <tr class="hover:bg-sky-50/40">
                                                <td class="px-4 py-2">
                                                    <p class="font-semibold text-slate-800">{{ $row['shakha'] }}</p>
                                                    <p class="text-[11px] text-slate-500">{{ $row['period'] }} · {{ $row['round'] }}</p>
                                                </td>
                                                <td class="px-4 py-2 text-slate-600">
                                                    <p>{{ $row['maker'] }}</p>
                                                    @if ($history['reviewer_id'] === 0)
                                                        <p class="text-[11px] text-slate-400">{{ $row['reviewer'] }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $pill[$row['tone']] }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $dot[$row['tone']] }}"></span>{{ $row['outcome'] }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ bd_datetime($row['at']) }}</td>
                                                <td class="px-4 py-2 text-right">
                                                    <a href="{{ route('audit-review.show', $row['report_id']) }}" class="{{ $btnSm }} {{ $tones['navy'] }}">Open</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center">
                            <p class="text-[14px] font-semibold text-navy-900">No reviews completed in {{ $history['year'] }}</p>
                            <p class="mt-1 text-[12px] text-slate-500">Reports appear here once they are sent back to the maker or confirmed.</p>
                        </div>
                    @endforelse
                @else
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @if ($tab === 'reviewed')
                            <p class="border-b border-slate-100 bg-sky-50/60 px-4 py-2 text-[11px] leading-relaxed text-sky-900">
                                <span class="font-semibold">Reviewers:</span> send ready reports to the maker for fixes, or confirm them as final.
                                <span class="font-semibold">Makers:</span> confirmed reports stay here as view only.
                            </p>
                        @elseif ($tab === 'returned')
                            <p class="border-b border-slate-100 bg-rose-50/60 px-4 py-2 text-[11px] text-rose-900">Fix the report in the Audit Report editor, then resubmit it for review.</p>
                        @endif
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-[12px]">
                                <thead class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                    <tr>
                                        <th class="px-4 py-2.5">Branch</th>
                                        <th class="px-4 py-2.5">Maker / Reviewer</th>
                                        <th class="px-4 py-2.5">Status</th>
                                        <th class="px-4 py-2.5">What to do</th>
                                        <th class="px-4 py-2.5">Submitted</th>
                                        <th class="px-4 py-2.5"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($reports as $report)
                                        @php
                                            $user = auth()->user();
                                            $canActRow = $reviews->canActAsReviewer($user, $report);
                                            $isReadyRow = $report->isInReview() && $report->isReviewReady() && $canActRow;
                                            $canReopen = $tab === 'reviewed'
                                                && $report->isReviewed()
                                                && (int) $report->reviewer_user_id === (int) $user->id;
                                            $todo = 'View only';
                                            if ($tab === 'inbox' && $canActRow) {
                                                $todo = 'Review & mark comments';
                                            } elseif ($tab === 'returned') {
                                                $todo = 'Edit report, then resubmit';
                                            } elseif ($tab === 'reviewed') {
                                                if ($isReadyRow) {
                                                    $todo = 'Send for fixes, or confirm';
                                                } elseif ($canReopen) {
                                                    $todo = 'Confirmed · reopen if needed';
                                                } elseif ($report->isReviewed()) {
                                                    $todo = 'Confirmed · view only';
                                                } else {
                                                    $todo = 'Open';
                                                }
                                            }
                                            $round = max(1, (int) $report->review_round);
                                            $statusTone = $report->isInReview()
                                                ? ($report->isReviewReady() ? 'sky' : 'amber')
                                                : ($report->isChangesRequested() ? 'rose' : 'emerald');
                                        @endphp
                                        <tr class="hover:bg-sky-50/40">
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#1b3a70] to-[#4a7fd0] text-[12px] font-semibold text-white shadow-sm">{{ mb_substr($report->entityDisplayName(), 0, 1) }}</span>
                                                    <div class="min-w-0">
                                                        <p class="font-semibold text-slate-800">{{ $report->entityDisplayName() }}</p>
                                                        <p class="text-[11px] text-slate-500">
                                                            {{ $report->periodLabel() }}
                                                            <span class="ml-1 inline-flex rounded-full px-1.5 text-[10px] font-semibold ring-1 {{ $round >= 2 ? $pill['amber'] : $pill['violet'] }}">{{ \App\Models\AuditReport::reviewRoundLabel($round) }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <p class="text-slate-700">{{ $report->user?->name ?: '—' }}</p>
                                                <p class="text-[11px] text-slate-400">{{ $report->reviewer?->name ?: 'No reviewer' }}</p>
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $pill[$statusTone] }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $dot[$statusTone] }}"></span>{{ $report->statusLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 font-medium text-slate-700">{{ $todo }}</td>
                                            <td class="whitespace-nowrap px-4 py-2.5 text-slate-500">{{ bd_datetime($report->submitted_for_review_at) }}</td>
                                            <td class="px-4 py-2.5">
                                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                    @if ($tab === 'inbox' && $canActRow)
                                                        <a href="{{ route('audit-review.show', $report) }}" class="{{ $btn }} {{ $tones['navy'] }}">Open to review</a>
                                                        <form
                                                            method="POST"
                                                            action="{{ route('audit-review.totally-fixed', $report) }}"
                                                            data-bynnas-confirm="This marks the report as Totally fixed · 100% perfect and locks it. No send-back to the maker."
                                                            data-bynnas-confirm-title="Grant as totally fixed?"
                                                            data-bynnas-confirm-ok="Totally fixed"
                                                            data-bynnas-confirm-tone="emerald"
                                                        >
                                                            @csrf
                                                            <button type="submit" class="{{ $btn }} {{ $tones['teal'] }}">Totally fixed</button>
                                                        </form>
                                                        <details class="relative">
                                                            <summary class="{{ $btn }} {{ $tones['rose'] }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">Return</summary>
                                                            <form
                                                                method="POST"
                                                                action="{{ route('audit-review.request-changes', $report) }}"
                                                                class="absolute right-0 z-20 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-[0_18px_40px_rgba(15,23,42,0.18)]"
                                                                data-bynnas-confirm="This returns the report to the maker with your note. They must fix and resubmit."
                                                                data-bynnas-confirm-title="Return to maker?"
                                                                data-bynnas-confirm-ok="Return to maker"
                                                                data-bynnas-confirm-tone="rose"
                                                            >
                                                                @csrf
                                                                <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Note for the maker</p>
                                                                <textarea name="body" rows="3" required class="mb-2 w-full rounded-lg border-slate-200 text-[12px]" placeholder="What should the maker fix?"></textarea>
                                                                <button type="submit" class="{{ $btn }} {{ $tones['rose'] }} w-full">Return to maker</button>
                                                            </form>
                                                        </details>
                                                    @elseif ($tab === 'returned')
                                                        <a href="{{ route('audits.index', ['report' => $report->id]) }}" class="{{ $btn }} {{ $tones['navy'] }}">Edit report</a>
                                                        <a href="{{ route('audit-review.show', $report) }}" class="{{ $btn }} {{ $tones['rose'] }}">View comments</a>
                                                    @elseif ($isReadyRow)
                                                        <a href="{{ route('audit-review.show', $report) }}" class="{{ $btn }} {{ $tones['violet'] }}">Edit marks</a>
                                                        <form
                                                            method="POST"
                                                            action="{{ route('audit-review.send-to-maker', $report) }}"
                                                            data-bynnas-confirm="{{ $report->user?->name ?: 'the maker' }} will fix based on your marks/comments, then resubmit for review. This does not lock the report."
                                                            data-bynnas-confirm-title="Send to {{ $report->user?->name ?: 'the maker' }} for fixes?"
                                                            data-bynnas-confirm-ok="Send for fixes"
                                                            data-bynnas-confirm-tone="sky"
                                                        >
                                                            @csrf
                                                            <button type="submit" class="{{ $btn }} {{ $tones['navy'] }}">Send to maker</button>
                                                        </form>
                                                        <form
                                                            method="POST"
                                                            action="{{ route('audit-review.approve', $report) }}"
                                                            data-bynnas-confirm="This locks the report as Confirmed. The maker cannot edit it further."
                                                            data-bynnas-confirm-title="Confirm this report?"
                                                            data-bynnas-confirm-ok="Confirm"
                                                            data-bynnas-confirm-tone="emerald"
                                                        >
                                                            @csrf
                                                            <button type="submit" class="{{ $btn }} {{ $tones['emerald'] }}">Confirm</button>
                                                        </form>
                                                        <form
                                                            method="POST"
                                                            action="{{ route('audit-review.totally-fixed', $report) }}"
                                                            data-bynnas-confirm="This marks the report as Totally fixed · 100% perfect and locks it."
                                                            data-bynnas-confirm-title="Grant as totally fixed?"
                                                            data-bynnas-confirm-ok="Totally fixed"
                                                            data-bynnas-confirm-tone="emerald"
                                                        >
                                                            @csrf
                                                            <button type="submit" class="{{ $btn }} {{ $tones['teal'] }}">Totally fixed</button>
                                                        </form>
                                                    @elseif ($canReopen)
                                                        <form
                                                            method="POST"
                                                            action="{{ route('audit-review.reopen', $report) }}"
                                                            data-bynnas-confirm="You can edit marks again, then Send to maker or Confirm."
                                                            data-bynnas-confirm-title="Reopen this confirmed report?"
                                                            data-bynnas-confirm-ok="Reopen"
                                                            data-bynnas-confirm-tone="amber"
                                                        >
                                                            @csrf
                                                            <button type="submit" class="{{ $btn }} {{ $tones['amber'] }}">Reopen</button>
                                                        </form>
                                                        <a href="{{ route('audit-review.show', $report) }}" class="{{ $btn }} {{ $tones['navy'] }}">Open</a>
                                                    @else
                                                        <a href="{{ route('audit-review.show', $report) }}" class="{{ $btn }} {{ $tones['navy'] }}">Open</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-12 text-center">
                                                <p class="text-[14px] font-semibold text-navy-900">Nothing here right now</p>
                                                <p class="mt-1 text-[12px] text-slate-500">Reports will appear in this tab when they need your attention.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if (method_exists($reports, 'links'))
                            <div class="border-t border-slate-100 px-4 py-2.5">{{ $reports->links() }}</div>
                        @endif
                    </section>
                @endif
            </main>

            {{-- Side rail: this month + things that need attention --}}
            @if ($monthlyStats || $actionNotes->isNotEmpty())
                <aside class="space-y-3 xl:sticky xl:top-3">
                    @if ($monthlyStats)
                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="bg-gradient-to-br from-teal-600 to-emerald-600 px-4 py-3 text-white">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-white/80">Reviewed by you</p>
                                <div class="mt-1 flex items-end justify-between gap-2">
                                    <p class="text-[28px] font-semibold leading-none tabular-nums">{{ $monthlyStats['completed_by_me'] ?? 0 }}</p>
                                    <p class="text-[12px] font-medium text-white/85">{{ $monthlyStats['period_label'] }}</p>
                                </div>
                            </div>
                            <dl class="divide-y divide-slate-100 px-4">
                                @foreach ([
                                    ['label' => '1st reviews', 'value' => $monthlyStats['first_reviews'], 'tone' => 'violet'],
                                    ['label' => 'Re-reviews', 'value' => $monthlyStats['re_reviews'], 'tone' => 'amber'],
                                    ['label' => 'Confirmed', 'value' => $monthlyStats['confirmed'], 'tone' => 'emerald'],
                                    ['label' => 'Awaiting review', 'value' => $monthlyStats['awaiting'], 'tone' => 'sky'],
                                ] as $kpi)
                                    <div class="flex items-center justify-between py-2">
                                        <dt class="flex items-center gap-2 text-[12px] text-slate-600">
                                            <span class="h-2 w-2 rounded-full {{ $dot[$kpi['tone']] }}"></span>{{ $kpi['label'] }}
                                        </dt>
                                        <dd class="text-[14px] font-semibold tabular-nums text-navy-900">{{ $kpi['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                            <form method="GET" action="{{ route('audit-review.index') }}" class="flex items-center gap-1.5 border-t border-slate-100 bg-slate-50/60 px-3 py-2">
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <select name="stats_month" aria-label="Month" class="h-7 min-w-0 flex-1 rounded-md border-slate-200 bg-white py-0 pl-2 pr-7 text-[12px] text-navy-900">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected((int) $monthlyStats['month'] === $m)>{{ \Carbon\Carbon::create(null, $m, 1)->format('M') }}</option>
                                    @endfor
                                </select>
                                <select name="stats_year" aria-label="Year" class="h-7 min-w-0 flex-1 rounded-md border-slate-200 bg-white py-0 pl-2 pr-7 text-[12px] text-navy-900">
                                    @for ($y = $thisYear - 1; $y <= $thisYear + 1; $y++)
                                        <option value="{{ $y }}" @selected((int) $monthlyStats['year'] === $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                                <button type="submit" class="{{ $btnSm }} {{ $tones['navy'] }}">Show</button>
                            </form>
                        </section>
                    @endif

                    <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Needs attention</p>
                        @forelse ($actionNotes as $note)
                            @php
                                $noteTone = in_array($note['tone'], ['amber', 'sky', 'rose'], true) ? $note['tone'] : 'emerald';
                                $noteBtn = $noteTone === 'sky' ? 'navy' : $noteTone;
                            @endphp
                            <div class="mb-2 rounded-xl border border-slate-100 bg-slate-50/60 p-2.5 last:mb-0">
                                <div class="flex items-start gap-2">
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $dot[$noteTone] }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12px] font-semibold leading-snug text-navy-900">{{ $note['title'] }}</p>
                                        <p class="mt-0.5 text-[11px] leading-snug text-slate-500">{{ $note['body'] }}</p>
                                    </div>
                                    @if (($note['count'] ?? 0) > 0)
                                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold text-white {{ $dot[$noteTone] }}">{{ $note['count'] }}</span>
                                    @endif
                                </div>
                                <a href="{{ $note['url'] }}" class="mt-2 w-full {{ $btnSm }} {{ $tones[$noteBtn] }}">{{ $note['action'] }}</a>
                            </div>
                        @empty
                            <a href="{{ $clearNote['url'] ?? route('audit-review.index') }}" class="flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-[12px] font-semibold text-emerald-800">
                                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-white">
                                    <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                </span>
                                All caught up
                            </a>
                        @endforelse
                    </section>
                </aside>
            @endif
        </div>
    </div>
</x-app-layout>
