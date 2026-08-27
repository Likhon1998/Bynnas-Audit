
<div class="mb-3 grid gap-2 sm:grid-cols-3">
    <div class="rounded-lg border border-sky-100 bg-sky-50/80 px-3 py-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700">Ongoing</p>
        <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-sky-900"><?php echo e($ongoingCount); ?></p>
        <p class="mt-0.5 text-[10px] text-sky-700/80">চলমান খসড়া</p>
    </div>
    <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-3 py-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700">Pending slots</p>
        <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-amber-900"><?php echo e($pendingSlots); ?></p>
        <p class="mt-0.5 text-[10px] text-amber-700/80">আরও নতুন (<?php echo e($maxConcurrentDrafts); ?> পর্যন্ত)</p>
    </div>
    <div class="rounded-lg border border-emerald-100 bg-emerald-50/80 px-3 py-2">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Completed</p>
        <p class="mt-0.5 text-[18px] font-bold tabular-nums leading-none text-emerald-900"><?php echo e($completedCount); ?></p>
        <p class="mt-0.5 text-[10px] text-emerald-700/80">সম্পন্ন সংরক্ষিত</p>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($canStartNewReport)): ?>
    <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] text-amber-900">
        চলমান রিপোর্ট <?php echo e($maxConcurrentDrafts); ?>টিতে পূর্ণ। নতুন শুরু করতে Continue করে শেষ করুন বা Delete করুন।
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="mb-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm <?php echo e($canStartNewReport ? '' : 'pointer-events-none opacity-60'); ?>">
    <div class="mb-2">
        <p class="text-[13px] font-semibold text-navy-900">নতুন রিপোর্ট শুরু করুন</p>
        <p class="text-[11px] text-slate-500">ক্লিক করলে সব শাখা · একসাথে ৫টি · বাকি স্ক্রল</p>
    </div>

    <div class="grid gap-2 lg:grid-cols-[minmax(0,1.4fr)_120px_88px_auto] lg:items-end">
        <div class="relative min-w-0" @mousedown.outside="open = false">
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Search branch</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input
                    type="search"
                    x-model="q"
                    @focus="open = true; highlight = 0"
                    @click="open = true"
                    @input="open = true; highlight = 0"
                    @keydown="onKey($event)"
                    placeholder="Click to browse, or type to filter…"
                    class="h-9 w-full rounded-lg border-slate-200 py-0 pl-8 pr-14 text-[12px] shadow-sm focus:border-[#2b579a] focus:ring-[#2b579a]"
                    autocomplete="off"
                >
                <button
                    type="button"
                    x-show="q || selectedId"
                    x-cloak
                    @click="clear()"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-slate-400 hover:text-slate-600"
                >Clear</button>
            </div>

            <div
                x-show="open"
                x-cloak
                class="absolute z-20 mt-1 w-full overflow-y-auto overscroll-contain rounded-lg border border-slate-200 bg-white shadow-lg"
                style="max-height: 200px;"
            >
                <template x-for="(b, idx) in filtered" :key="b.id">
                    <button
                        type="button"
                        @click="pick(b)"
                        @mouseenter="highlight = idx"
                        class="flex h-10 w-full shrink-0 items-center gap-2 px-2.5 text-left hover:bg-sky-50"
                        :class="highlight === idx ? 'bg-sky-50' : ''"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[12px] font-semibold leading-tight text-navy-900" x-text="b.name"></span>
                            <span class="block truncate text-[10px] leading-tight text-slate-500">
                                <span x-text="b.code || '—'"></span>
                                <span x-show="b.area"> · </span>
                                <span x-text="b.area || ''"></span>
                            </span>
                        </span>
                        <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-semibold"
                            :class="b.active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                            x-text="b.active ? 'Active' : 'Inactive'"
                        ></span>
                    </button>
                </template>
                <p x-show="filtered.length === 0" class="px-2.5 py-2 text-[11px] text-slate-500">কোনো শাখা মেলেনি</p>
            </div>

            <p x-show="selectedLabel" x-cloak class="mt-1 truncate text-[11px] font-medium text-emerald-700">
                Selected: <span x-text="selectedLabel"></span>
            </p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['shakha_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-[11px] font-medium text-rose-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Month</label>
            <select wire:model="report_month" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]" <?php if(! $canStartNewReport): echo 'disabled'; endif; ?>>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($m); ?>"><?php echo e(date('F', mktime(0, 0, 0, $m, 1))); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <div>
            <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Year</label>
            <select wire:model="report_year" class="h-9 w-full rounded-lg border-slate-200 py-0 text-[12px]" <?php if(! $canStartNewReport): echo 'disabled'; endif; ?>>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($y = now()->year + 1; $y >= now()->year - 6; $y--): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <div class="flex items-end">
            <button
                type="button"
                wire:click="startReport"
                wire:loading.attr="disabled"
                wire:target="startReport"
                <?php if(! $canStartNewReport): echo 'disabled'; endif; ?>
                class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072] disabled:cursor-not-allowed disabled:opacity-50 lg:w-auto"
            >
                <span wire:loading.remove wire:target="startReport">Start new</span>
                <span wire:loading wire:target="startReport">Starting…</span>
            </button>
        </div>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ongoingReports->isNotEmpty()): ?>
    <div class="mb-3 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-3 py-2">
            <p class="text-[13px] font-semibold text-navy-900">চলমান রিপোর্ট</p>
            <p class="text-[10px] text-slate-500">Continue · Auto-save চালু</p>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ongoingReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex flex-wrap items-center gap-2 px-3 py-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[12px] font-semibold text-navy-900">
                            <?php echo e($report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch')); ?>

                        </p>
                        <p class="mt-0.5 truncate text-[10px] text-slate-500">
                            <?php echo e($report->periodLabel()); ?>

                            · <?php echo e($report->statusBadge()); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->last_saved_at): ?>
                                · Saved <?php echo e($report->last_saved_at->timezone('Asia/Dhaka')->format('d M, h:i A')); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="h-1 max-w-[180px] flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#2b579a]" style="width: <?php echo e(min(100, (int) $report->progress_pct)); ?>%"></div>
                            </div>
                            <span class="text-[10px] font-medium tabular-nums text-slate-500"><?php echo e($report->progress_pct); ?>%</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            wire:click="resumeReport(<?php echo e($report->id); ?>)"
                            class="h-7 rounded-md bg-[#2b579a] px-2.5 text-[11px] font-semibold text-white hover:bg-[#204072]"
                        >Continue</button>
                        <a
                            href="<?php echo e(route('audit-findings.entry', ['report' => $report->id])); ?>"
                            class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                        >Findings</a>
                        <button
                            type="button"
                            wire:click="deleteDraft(<?php echo e($report->id); ?>)"
                            wire:confirm="এই খসড়া মুছে ফেলবেন?"
                            class="h-7 rounded-md border border-rose-200 px-2.5 text-[11px] font-medium text-rose-600 hover:bg-rose-50"
                        >Delete</button>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($completedReports->isNotEmpty()): ?>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-3 py-2">
            <p class="text-[13px] font-semibold text-navy-900">সম্প্রতি সম্পন্ন</p>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $completedReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex flex-wrap items-center gap-2 px-3 py-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[12px] font-semibold text-navy-900">
                            <?php echo e($report->shakha_display_name ?: ($report->shakha?->name ?? 'Branch')); ?>

                        </p>
                        <p class="mt-0.5 truncate text-[10px] text-slate-500">
                            <?php echo e($report->periodLabel()); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->completed_at): ?>
                                · <?php echo e($report->completed_at->format('d M Y')); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a
                            href="<?php echo e(route('audit-findings.entry', ['report' => $report->id])); ?>"
                            class="inline-flex h-7 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2.5 text-[11px] font-medium text-emerald-800 hover:bg-emerald-100"
                        >Findings</a>
                        <button
                            type="button"
                            wire:click="resumeReport(<?php echo e($report->id); ?>)"
                            class="h-7 rounded-md border border-slate-200 px-2.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                        >Open</button>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-reports-dashboard.blade.php ENDPATH**/ ?>