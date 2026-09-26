<x-app-layout>
    <div class="px-3 py-3 lg:px-5" x-data="visitRoster()">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <a href="{{ route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="text-[12px] font-medium text-[#2b579a] hover:underline">← Monthly Field Visits</a>
                <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Who visits where</h1>
                <p class="text-[12px] text-slate-500">FY {{ $plan->fy_label }} · {{ $monthLabel }}. One row per shakha. Person is who is allocated to that place. Last audit is the month it was visited before this one.</p>
            </div>
            <form method="GET" action="{{ route('monthly-visits.people') }}" class="flex items-center gap-1.5">
                <select name="fy" class="h-8 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px]" onchange="this.form.submit()">
                    @foreach ($availablePlans as $p)
                        <option value="{{ $p->fy_label }}" @selected($p->fy_label === $plan->fy_label)>{{ $p->fy_label }}</option>
                    @endforeach
                </select>
                <select name="month" class="h-8 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px]" onchange="this.form.submit()">
                    @foreach ($monthOptions as $opt)
                        <option value="{{ $opt['index'] }}" @selected((int) $opt['index'] === (int) $monthIndex)>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5">
                <select x-model="personId" class="h-8 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px]" title="Person">
                    <option value="">Everyone</option>
                    @foreach ($peopleRoster as $person)
                        <option value="{{ $person['id'] }}">{{ $person['name'] }}@if ($person['title'] !== '') · {{ $person['title'] }}@endif</option>
                    @endforeach
                </select>
                <select x-model="visitType" class="h-8 rounded-md border-slate-200 py-0 pl-2 pr-7 text-[12px]" title="Visit type">
                    <option value="">All types</option>
                    @foreach ($visitTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('monthly-visits.people.pdf', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="inline-flex h-8 items-center rounded-md border border-rose-200 bg-rose-50 px-2.5 text-[12px] font-semibold text-rose-800 hover:bg-rose-100">PDF · everyone</a>
                <a href="{{ route('monthly-visits.people.doc', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}" class="inline-flex h-8 items-center rounded-md border border-sky-200 bg-sky-50 px-2.5 text-[12px] font-semibold text-sky-800 hover:bg-sky-100">DOC · everyone</a>
                <a
                    :href="personId ? '{{ route('monthly-visits.people.pdf', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}&person=' + personId : '#'"
                    :class="personId ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-40'"
                    class="inline-flex h-8 items-center rounded-md border border-rose-200 bg-white px-2.5 text-[12px] font-semibold text-rose-800 hover:bg-rose-50"
                >PDF · this person</a>
                <a
                    :href="personId ? '{{ route('monthly-visits.people.doc', ['fy' => $plan->fy_label, 'month' => $monthIndex]) }}&person=' + personId : '#'"
                    :class="personId ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-40'"
                    class="inline-flex h-8 items-center rounded-md border border-sky-200 bg-white px-2.5 text-[12px] font-semibold text-sky-800 hover:bg-sky-50"
                >DOC · this person</a>
            </div>
        </div>

        @include('monthly-visits.partials.people-tables', ['interactive' => true])
    </div>

    <script>
        function visitRoster() {
            return {
                personId: '',
                visitType: '',
                rowVisible(el) {
                    if (this.personId) {
                        const ids = (' ' + (el.dataset.personIds || '') + ' ');
                        if (!ids.includes(' ' + this.personId + ' ')) return false;
                    }
                    if (this.visitType && el.dataset.type !== this.visitType) return false;
                    return true;
                },
                shownIndex(el) {
                    const rows = Array.from(el.closest('tbody').querySelectorAll('[data-visit-row]')).filter((row) => this.rowVisible(row));
                    const index = rows.indexOf(el);
                    return index < 0 ? '' : String(index + 1);
                },
                visibleCount(which) {
                    const root = document.querySelector('[data-roster="' + which + '"]');
                    if (!root) return 0;
                    let n = 0;
                    root.querySelectorAll('[data-visit-row]').forEach((el) => { if (this.rowVisible(el)) n++; });
                    return n;
                },
            };
        }
    </script>
</x-app-layout>
