<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div
        class="px-4 py-4 lg:px-6"
        style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;"
        x-data="{
            employeesByShakha: <?php echo \Illuminate\Support\Js::from($employeesByShakha ?? [])->toHtml() ?>,
            rows: <?php echo \Illuminate\Support\Js::from($branchRows ?? [])->toHtml() ?>,
            openFor: null,
            staffQ: '',
            staffHighlight: 0,
            savingId: null,
            savedId: null,
            dropdownStyle: {},
            csrf: <?php echo \Illuminate\Support\Js::from(csrf_token())->toHtml() ?>,
            employeesFor(shakhaId) {
                return this.employeesByShakha[String(shakhaId)] || this.employeesByShakha[shakhaId] || [];
            },
            placeDropdown(el) {
                if (! el) return;
                const r = el.getBoundingClientRect();
                const width = Math.max(r.width, 176);
                const menuHeight = 192;
                const spaceBelow = window.innerHeight - r.bottom;
                const openUp = spaceBelow < menuHeight && r.top > menuHeight;
                const top = openUp ? Math.max(8, r.top - menuHeight - 4) : r.bottom + 4;
                this.dropdownStyle = {
                    position: 'fixed',
                    left: Math.min(r.left, window.innerWidth - width - 8) + 'px',
                    top: top + 'px',
                    width: width + 'px',
                    zIndex: '9999',
                };
            },
            openStaff(row, el) {
                this.openFor = row.id;
                this.staffQ = row.responsible_staff_name || '';
                this.staffHighlight = 0;
                this.$nextTick(() => this.placeDropdown(el));
            },
            filterEmployees(shakhaId, q) {
                const list = this.employeesFor(shakhaId);
                const needle = (q || '').trim().toLowerCase();
                if (!needle) return list.slice(0, 8);
                return list.filter((e) => {
                    const hay = (e.code + ' ' + e.name + ' ' + (e.designation || '')).toLowerCase();
                    return hay.includes(needle);
                }).slice(0, 8);
            },
            resolveName(shakhaId, raw) {
                const needle = (raw || '').trim();
                if (!needle) return '';
                const list = this.employeesFor(shakhaId);
                const exact = list.find((e) =>
                    e.code.toLowerCase() === needle.toLowerCase()
                    || e.name.trim().toLowerCase() === needle.toLowerCase()
                );
                if (exact) return exact.name;
                const first = this.filterEmployees(shakhaId, needle)[0];
                return first ? first.name : needle;
            },
            pickStaff(row, emp) {
                row.responsible_staff_name = emp.name;
                this.staffQ = emp.name;
                this.openFor = null;
                this.saveStaff(row);
            },
            async saveStaff(row) {
                const resolved = this.resolveName(row.shakha_id, row.responsible_staff_name);
                row.responsible_staff_name = resolved;
                this.openFor = null;
                this.savingId = row.id;
                try {
                    const res = await fetch(row.staff_save_url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ responsible_staff_name: resolved }),
                    });
                    if (!res.ok) throw new Error('save failed');
                    const data = await res.json();
                    row.responsible_staff_name = data.responsible_staff_name || '';
                    this.savedId = row.id;
                    setTimeout(() => { if (this.savedId === row.id) this.savedId = null; }, 1600);
                } catch (e) {
                    alert('Could not save staff. Try again.');
                } finally {
                    this.savingId = null;
                }
            },
            onStaffKey(e, row) {
                const list = this.filterEmployees(row.shakha_id, this.staffQ);
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.openFor = row.id;
                    this.staffHighlight = Math.min(this.staffHighlight + 1, Math.max(list.length - 1, 0));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.staffHighlight = Math.max(this.staffHighlight - 1, 0);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (list[this.staffHighlight]) this.pickStaff(row, list[this.staffHighlight]);
                    else this.saveStaff(row);
                } else if (e.key === 'Escape') {
                    this.openFor = null;
                }
            }
        }"
    >
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('audit-findings.index', ['month' => $month, 'year' => $year])); ?>" class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-[15px] font-semibold text-navy-900"><?php echo e($indicator->indicator_code); ?> — <?php echo e($indicator->title); ?></h1>
                <p class="text-[11px] text-slate-500">
                    <?php echo e($indicator->category ?: '—'); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($indicator->sub_category): ?>
                        · <?php echo e($indicator->sub_category); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    · <?php echo e($indicator->risk_rating ?: '—'); ?>

                    · <?php echo e(date('F', mktime(0, 0, 0, $month, 1))); ?> <?php echo e($year); ?>

                </p>
                <p class="mt-0.5 text-[10px] text-slate-400">Staff: type employee ID or name from that branch’s Shakha Employees, then Enter / blur to save.</p>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orgRow): ?>
            <div class="mb-3 grid gap-2 sm:grid-cols-4">
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Total amount</p>
                    <p class="text-[14px] font-bold tabular-nums text-navy-900"><?php echo e(number_format($orgRow->total_amount, 2)); ?></p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Samples</p>
                    <p class="text-[14px] font-bold tabular-nums text-navy-900"><?php echo e($orgRow->total_samples_checked); ?></p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Irregularities</p>
                    <p class="text-[14px] font-bold tabular-nums text-rose-700"><?php echo e($orgRow->total_irregularities); ?></p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Objected branches</p>
                    <p class="text-[14px] font-bold tabular-nums text-navy-900"><?php echo e($orgRow->objected_branch_count); ?></p>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-3 py-2">
                <p class="text-[12px] font-semibold text-navy-900">Branch blocks (Excel X-axis)</p>
                <p class="text-[10px] text-slate-500">Only branches with stored finding cells · <?php echo e(count($branchRows ?? [])); ?> rows</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="border-b border-slate-200 px-3 py-2">Branch</th>
                            <th class="border-b border-slate-200 px-3 py-2">Area</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Amount</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Samples</th>
                            <th class="border-b border-slate-200 px-3 py-2 text-right">Irregularities</th>
                            <th class="border-b border-slate-200 px-3 py-2">Observation</th>
                            <th class="border-b border-slate-200 px-3 py-2 min-w-[180px]">Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in rows" :key="row.id">
                            <tr class="border-b border-slate-50 align-top">
                                <td class="px-3 py-2 font-medium text-navy-900">
                                    <span x-text="row.shakha_name"></span>
                                    <span class="mt-0.5 block font-mono text-[10px] text-slate-400" x-text="row.shakha_code"></span>
                                </td>
                                <td class="px-3 py-2 text-[11px] text-slate-600" x-text="row.area_name"></td>
                                <td class="px-3 py-2 text-right tabular-nums" x-text="row.amount"></td>
                                <td class="px-3 py-2 text-right tabular-nums" x-text="row.sample_size_checked"></td>
                                <td class="px-3 py-2 text-right tabular-nums font-semibold text-rose-700" x-text="row.irregularity_count"></td>
                                <td class="max-w-[280px] px-3 py-2 text-[11px] text-slate-600" x-text="row.observation"></td>
                                <td class="px-3 py-2" @click.outside="if (openFor === row.id) openFor = null">
                                    <div class="relative w-44">
                                        <input
                                            type="text"
                                            class="h-8 w-full rounded-md border-slate-200 py-0 text-[12px]"
                                            placeholder="ID / name…"
                                            autocomplete="off"
                                            x-model="row.responsible_staff_name"
                                            @focus="openStaff(row, $event.target)"
                                            @input="openStaff(row, $event.target); staffQ = $event.target.value; staffHighlight = 0"
                                            @keydown="onStaffKey($event, row)"
                                            @blur="setTimeout(() => { if (openFor === row.id) saveStaff(row); }, 140)"
                                        >
                                        <p class="mt-0.5 text-[9px] text-emerald-600" x-show="savedId === row.id" x-cloak>Saved</p>
                                        <p class="mt-0.5 text-[9px] text-slate-400" x-show="savingId === row.id" x-cloak>Saving…</p>
                                        <template x-teleport="body">
                                            <div
                                                x-show="openFor === row.id"
                                                x-cloak
                                                x-transition.opacity.duration.100ms
                                                :style="dropdownStyle"
                                                class="max-h-48 overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-xl"
                                                @mousedown.prevent
                                            >
                                                <template x-if="employeesFor(row.shakha_id).length === 0">
                                                    <p class="px-2.5 py-2 text-[11px] text-amber-700">No employees for this branch. Add them under Shakha Employees.</p>
                                                </template>
                                                <template x-for="(emp, idx) in filterEmployees(row.shakha_id, staffQ)" :key="emp.id">
                                                    <button
                                                        type="button"
                                                        class="flex w-full flex-col items-start gap-0.5 px-2.5 py-1.5 text-left hover:bg-sky-50"
                                                        :class="idx === staffHighlight ? 'bg-sky-50' : ''"
                                                        @mousedown.prevent="pickStaff(row, emp)"
                                                    >
                                                        <span class="text-[12px] font-semibold text-navy-900" x-text="emp.name"></span>
                                                        <span class="text-[10px] text-slate-500">
                                                            <span class="font-mono" x-text="emp.code"></span>
                                                            <span x-show="emp.designation"> · <span x-text="emp.designation"></span></span>
                                                        </span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="7" class="px-3 py-10 text-center text-[12px] text-slate-400">No branch findings for this indicator in the selected period.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audit-findings\show.blade.php ENDPATH**/ ?>