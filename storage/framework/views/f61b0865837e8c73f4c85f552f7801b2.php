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

    <div class="px-3 py-3 lg:px-5" style="font-family:'Hind Siliguri','Nirmala UI',system-ui,sans-serif;">
        <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="<?php echo e(route('audits.index')); ?>" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to Audit Reports</a>
                <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">Report send history</h1>
                <p class="mt-0.5 text-[12px] text-slate-500">Emails sent for reports in <?php echo e($periodLabel); ?></p>
            </div>
            <div class="ml-auto flex flex-wrap items-center justify-end gap-1.5">
                <form method="GET" action="<?php echo e(route('audits.send-history')); ?>" class="flex flex-wrap items-center gap-1.5">
                    <select name="month" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($m); ?>" <?php if($m === $month): echo 'selected'; endif; ?>><?php echo e(date('F', mktime(0, 0, 0, $m, 1))); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <select name="year" class="h-8 rounded-md border-slate-200 py-0 text-[12px]" onchange="this.form.submit()">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $yearOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($y); ?>" <?php if($y === $year): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </form>
                <span class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-800">
                    Sent <span class="tabular-nums"><?php echo e($sentCount); ?></span>
                </span>
                <span class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-800">
                    Failed <span class="tabular-nums"><?php echo e($failedCount); ?></span>
                </span>
            </div>
        </div>

        <div class="mb-3 grid grid-cols-4 gap-1 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm sm:grid-cols-6 lg:grid-cols-12">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monthStrip; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    href="<?php echo e($chip['url']); ?>"
                    class="rounded-md px-1 py-1.5 text-center text-[11px] font-semibold transition
                        <?php echo e($chip['active'] ? 'bg-navy-900 text-white' : ($chip['has_data'] ? 'bg-sky-50 text-sky-900 hover:bg-sky-100' : 'text-slate-400 hover:bg-slate-50')); ?>"
                ><?php echo e($chip['label']); ?></a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $send): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $reportLabel = trim((string) ($send->report?->shakha_display_name ?: $send->report?->shakha?->name ?: 'Report'));
                                $period = $send->report?->periodLabel() ?: '—';
                            ?>
                            <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-slate-50/60'); ?> hover:bg-sky-50/40">
                                <td class="whitespace-nowrap px-3 py-2.5 tabular-nums text-slate-600">
                                    <?php echo e(optional($send->sent_at)->timezone('Asia/Dhaka')->format('d M Y, h:i A') ?: '—'); ?>

                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="font-medium text-slate-800"><?php echo e($reportLabel); ?></p>
                                    <p class="text-[10px] text-slate-400"><?php echo e($period); ?></p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="text-slate-700"><?php echo e($send->from_name); ?></p>
                                    <p class="text-[10px] text-slate-400"><?php echo e($send->from_email); ?></p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <p class="text-slate-700"><?php echo e($send->to_email); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($send->cc_email): ?>
                                        <p class="text-[10px] text-slate-400">CC: <?php echo e($send->cc_email); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 align-top text-slate-600">
                                    <p class="line-clamp-2" title="<?php echo e($send->subject); ?>"><?php echo e($send->subject); ?></p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($send->attached_pdf): ?>
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Yes</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-500">No</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($send->status === 'sent'): ?>
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Sent</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700" title="<?php echo e($send->error_message); ?>">Failed</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($send->error_message): ?>
                                            <p class="mt-1 max-w-[180px] text-[10px] leading-snug text-rose-500"><?php echo e(\Illuminate\Support\Str::limit($send->error_message, 80)); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="px-3 py-12 text-center text-[12px] text-slate-400">
                                    No emails sent for <?php echo e($periodLabel); ?>. Complete a report, then use <span class="font-semibold text-slate-600">Send by Gmail</span>.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sends->isNotEmpty()): ?>
                <div class="border-t border-slate-100 bg-slate-50/50 px-3 py-1.5 text-[10px] text-slate-500 sm:px-4">
                    Showing <?php echo e($sends->count()); ?> · <?php echo e($sentCount); ?> sent · <?php echo e($failedCount); ?> failed
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/send-history.blade.php ENDPATH**/ ?>