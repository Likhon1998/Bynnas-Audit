{{-- Accessible glassmorphic slide-over for Shakha territory details. --}}
<div
    x-data="shakhaDrawer()"
    x-on:open-shakha-drawer.window="open($event.detail)"
    x-on:keydown.escape.window="if (isOpen) close()"
>
    <div class="sr-only" aria-live="polite" aria-atomic="true" x-text="announce"></div>

    <div
        x-show="isOpen"
        x-cloak
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[700] bg-slate-950/55 backdrop-blur-sm"
        @click="close()"
        aria-hidden="true"
    ></div>

    <aside
        x-show="isOpen"
        x-cloak
        x-trap="isOpen"
        x-trap.noscroll="isOpen"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        :aria-describedby="descId"
        class="fixed inset-y-0 right-0 z-[710] flex w-full max-w-md flex-col border-l border-white/10 bg-slate-900/85 text-white shadow-2xl shadow-black/40 backdrop-blur-md"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.stop
    >
        <header class="flex items-start gap-3 border-b border-white/10 px-5 py-4">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Shakha territory</p>
                <h2 :id="titleId" class="mt-1 truncate text-[18px] font-semibold text-white" x-text="shakha.name || 'Shakha'"></h2>
                <p :id="descId" class="mt-0.5 truncate text-[12px] text-slate-400" x-text="metaLine"></p>
            </div>
            <span
                class="mt-1 inline-flex shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide"
                :class="riskChipClass"
                x-text="shakha.risk_label || 'Not assessed'"
            ></span>
            <button
                type="button"
                class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-slate-200 hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-300"
                @click="close()"
                aria-label="Close shakha details"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-5 py-5">
            <dl class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3">
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Assigned auditor</dt>
                    <dd class="mt-1 text-[13px] font-medium text-white" x-text="shakha.auditor || shakha.focal || 'Unassigned'"></dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3">
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Compliance score</dt>
                    <dd class="mt-1 text-[18px] font-semibold tabular-nums text-white">
                        <span x-text="shakha.compliance_score == null ? '—' : shakha.compliance_score"></span>
                        <span class="text-[11px] font-medium text-slate-400" x-show="shakha.compliance_score != null"> / 100</span>
                    </dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3">
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Total findings</dt>
                    <dd class="mt-1 text-[18px] font-semibold tabular-nums text-white" x-text="shakha.findings_count ?? 0"></dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-3">
                    <dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Completed reports</dt>
                    <dd class="mt-1 text-[18px] font-semibold tabular-nums text-white" x-text="shakha.completed ?? 0"></dd>
                </div>
            </dl>

            <section aria-labelledby="priority-issues-heading">
                <h3 id="priority-issues-heading" class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Priority issues</h3>
                <ul class="mt-2 space-y-2" x-show="issues.length">
                    <template x-for="(issue, index) in issues" :key="index">
                        <li class="rounded-xl border border-white/10 bg-white/5 px-3 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[13px] font-medium text-white" x-text="issue.title"></p>
                                <span class="shrink-0 rounded-full bg-rose-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase text-rose-200" x-text="issue.severity || 'issue'"></span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400" x-show="issue.amount != null">
                                Amount <span class="tabular-nums text-slate-200" x-text="formatAmount(issue.amount)"></span>
                                <span x-show="issue.count"> · <span class="tabular-nums" x-text="issue.count"></span> irregularities</span>
                            </p>
                            <p class="mt-1 text-[12px] leading-relaxed text-slate-300" x-show="issue.detail" x-text="issue.detail"></p>
                        </li>
                    </template>
                </ul>
                <p class="mt-2 rounded-xl border border-dashed border-white/10 px-3 py-4 text-[12px] text-slate-400" x-show="!issues.length">
                    No priority issues recorded for this shakha.
                </p>
            </section>
        </div>

        <footer class="border-t border-white/10 px-5 py-3" x-show="shakha.url">
            <a
                :href="shakha.url"
                class="flex items-center justify-center rounded-lg bg-sky-400/20 px-3 py-2 text-[12px] font-semibold text-sky-100 ring-1 ring-sky-300/30 hover:bg-sky-400/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-300"
            >
                Open shakha record
            </a>
        </footer>
    </aside>
</div>

@once
<script>
function shakhaDrawer() {
    return {
        isOpen: false,
        shakha: {},
        announce: '',
        titleId: 'shakha-drawer-title',
        descId: 'shakha-drawer-desc',
        get issues() {
            return Array.isArray(this.shakha.issues) ? this.shakha.issues : [];
        },
        get metaLine() {
            return [this.shakha.area, this.shakha.district || this.shakha.upazila, this.shakha.division]
                .filter(Boolean)
                .join(' · ');
        },
        get riskChipClass() {
            const risk = this.shakha.risk;
            if (risk === 'high' || risk === 'significant') return 'bg-rose-500/20 text-rose-200';
            if (risk === 'medium') return 'bg-amber-500/20 text-amber-200';
            if (risk === 'low') return 'bg-emerald-500/20 text-emerald-200';
            return 'bg-slate-500/20 text-slate-200';
        },
        open(detail) {
            this.shakha = detail && typeof detail === 'object' ? detail : {};
            this.isOpen = true;
            this.announce = (this.shakha.name || 'Shakha') + ' details opened';
        },
        close() {
            if (!this.isOpen) return;
            this.isOpen = false;
            this.announce = 'Shakha details closed';
        },
        formatAmount(value) {
            const number = Number(value);
            if (!Number.isFinite(number)) return '—';
            return number.toLocaleString('en-BD', { maximumFractionDigits: 0 });
        },
    };
}
</script>
@endonce
