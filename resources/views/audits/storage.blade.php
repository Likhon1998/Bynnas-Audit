<x-app-layout>
    <div
        class="min-h-full bg-[radial-gradient(circle_at_top,_#fff7ed_0,_#f8fafc_42%)] px-3 py-3 lg:px-5"
        x-data="{
            folders: @js($folders),
            openKey: null,
            get openFolder() {
                return this.folders.find((folder) => folder.key === this.openKey) || null;
            }
        }"
    >
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-amber-700">Storehouse</p>
                <h1 class="text-base font-semibold tracking-tight text-navy-900">Report storage</h1>
                <p class="mt-0.5 text-[12px] text-slate-600">A report is filed here as a PDF only after it is marked done at 100%. You can still open it to edit.</p>
            </div>
            <p class="text-[12px] font-medium text-slate-500">{{ $total }} {{ $total === 1 ? 'report' : 'reports' }}</p>
        </div>

        <div class="mb-3 flex flex-wrap items-center gap-1 text-[12px] text-slate-500">
            <button type="button" class="font-semibold text-amber-800 hover:underline" @click="openKey = null">Report storage</button>
            <template x-if="openFolder">
                <span>
                    <span class="mx-1 text-slate-300">/</span>
                    <span class="font-semibold text-navy-900" x-text="openFolder.label"></span>
                </span>
            </template>
        </div>

        <template x-if="folders.length === 0">
            <div class="rounded-2xl border border-dashed border-amber-200 bg-white/80 px-4 py-12 text-center">
                <p class="text-sm font-semibold text-navy-900">No folders yet</p>
                <p class="mt-1 text-[12px] text-slate-500">Finished reports (done · 100%) are stored here as PDF files, one folder per month.</p>
            </div>
        </template>

        <div x-show="!openKey && folders.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
            <template x-for="folder in folders" :key="folder.key">
                <button
                    type="button"
                    @click="openKey = folder.key"
                    class="group flex flex-col items-start rounded-2xl border border-amber-100 bg-white p-3 text-left shadow-[0_10px_28px_rgba(180,83,9,0.08)] transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-[0_16px_36px_rgba(180,83,9,0.14)]"
                >
                    <span class="flex h-12 w-14 items-center justify-center rounded-lg bg-gradient-to-b from-amber-300 to-amber-500 text-amber-950 shadow-inner">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 7.2A2.2 2.2 0 015.2 5h4.1c.5 0 1 .2 1.4.6l1.1 1.2c.3.3.7.5 1.1.5H18.8A2.2 2.2 0 0121 9.5v7.3A2.2 2.2 0 0118.8 19H5.2A2.2 2.2 0 013 16.8V7.2z"/>
                        </svg>
                    </span>
                    <span class="mt-3 block text-[13px] font-semibold text-navy-900" x-text="folder.label"></span>
                    <span class="mt-0.5 text-[12px] text-slate-500" x-text="folder.count + (folder.count === 1 ? ' report' : ' reports')"></span>
                </button>
            </template>
        </div>

        <div x-show="openFolder" x-cloak class="rounded-2xl border border-amber-100 bg-white/90 p-3 shadow-[0_10px_28px_rgba(15,23,42,0.05)]">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="text-[14px] font-semibold text-navy-900" x-text="openFolder ? openFolder.label : ''"></h2>
                <button type="button" class="text-[12px] font-semibold text-amber-800 hover:underline" @click="openKey = null">All folders</button>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="report in (openFolder ? openFolder.reports : [])" :key="report.id">
                    <div class="flex items-start gap-2 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-white text-sky-700 ring-1 ring-slate-200">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2.75A1.75 1.75 0 018.75 1h5.2c.46 0 .9.18 1.23.51l3.31 3.31c.33.33.51.77.51 1.23v12.2A1.75 1.75 0 0117.25 20h-8.5A1.75 1.75 0 017 18.25V2.75z"/></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13px] font-semibold text-navy-900" x-text="report.name"></span>
                            <span class="mt-0.5 block truncate text-[12px] text-slate-500" x-text="report.status + (report.memo ? ' · ' + report.memo : '')"></span>
                        </span>
                        <span class="flex shrink-0 flex-col gap-1">
                            <a :href="report.pdf_url" class="inline-flex h-7 items-center rounded-md bg-amber-600 px-2 text-[11px] font-semibold text-white hover:bg-amber-700" @click.stop>PDF</a>
                            <a :href="report.edit_url" class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50" @click.stop>Edit</a>
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
