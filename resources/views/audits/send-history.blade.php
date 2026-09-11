<x-app-layout>
    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('audits.index') }}" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to Audit Reports</a>
                <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">Report send history</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Emails sent for reports in {{ $periodLabel }}</p>
            </div>
            <div class="ml-auto flex flex-wrap items-center justify-end gap-1.5">
                <form method="GET" action="{{ route('audits.send-history') }}" class="flex flex-wrap items-center gap-1.5">
                    <select name="month" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $month)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                    <select name="year" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        @foreach ($yearOptions as $y)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-800">
                    Sent <span class="tabular-nums">{{ $sentCount }}</span>
                </span>
                <span class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-800">
                    Failed <span class="tabular-nums">{{ $failedCount }}</span>
                </span>
            </div>
        </div>

        <div class="mb-3 grid grid-cols-4 gap-1 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm sm:grid-cols-6 lg:grid-cols-12">
            @foreach ($monthStrip as $chip)
                <a
                    href="{{ $chip['url'] }}"
                    class="rounded-md px-1 py-1.5 text-center text-[11px] font-semibold transition
                        {{ $chip['active'] ? 'bg-navy-900 text-white' : ($chip['has_data'] ? 'bg-sky-50 text-sky-900 hover:bg-sky-100' : 'text-slate-400 hover:bg-slate-50') }}"
                >{{ $chip['label'] }}</a>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#0B1F36] text-[10px] font-semibold uppercase tracking-wide text-slate-200">
                            <th class="px-3 py-2.5">When</th>
                            <th class="px-3 py-2.5">Report</th>
                            <th class="px-3 py-2.5">From</th>
                            <th class="px-3 py-2.5">To</th>
                            <th class="px-3 py-2.5 min-w-[180px]">Subject</th>
                            <th class="px-3 py-2.5">PDF</th>
                            <th class="px-3 py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sends as $index => $send)
                            @php
                                $reportLabel = trim((string) ($send->report?->shakha_display_name ?: $send->report?->shakha?->name ?: 'Report'));
                                $period = $send->report?->periodLabel() ?: '—';
                            @endphp
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/60' }} hover:bg-sky-50/40">
                                <td class="whitespace-nowrap px-3 py-2.5 tabular-nums text-slate-600">
                                    {{ optional($send->sent_at)->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?: '—' }}
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="font-medium text-slate-800">{{ $reportLabel }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $period }}</p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="text-slate-700">{{ $send->from_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $send->from_email }}</p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="text-slate-700">{{ $send->to_email }}</p>
                                    @if ($send->cc_email)
                                        <p class="text-[10px] text-slate-400">CC: {{ $send->cc_email }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 align-top text-slate-600">
                                    <p class="line-clamp-2" title="{{ $send->subject }}">{{ $send->subject }}</p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    @if ($send->attached_pdf)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Yes</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-500">No</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    @if ($send->status === 'sent')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Sent</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700" title="{{ $send->error_message }}">Failed</span>
                                        @if ($send->error_message)
                                            <p class="mt-1 max-w-[180px] text-[10px] leading-snug text-rose-500">{{ \Illuminate\Support\Str::limit($send->error_message, 80) }}</p>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-12 text-center text-[12px] text-slate-400">
                                    No emails sent for {{ $periodLabel }}. Complete a report, then use <span class="font-semibold text-slate-600">Send by Gmail</span>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($sends->isNotEmpty())
                <div class="border-t border-slate-100 bg-slate-50/50 px-3 py-1.5 text-[10px] text-slate-500 sm:px-4">
                    Showing {{ $sends->count() }} · {{ $sentCount }} sent · {{ $failedCount }} failed
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
