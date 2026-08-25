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
    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">All Shakha</h1>
                <p class="mt-0.5 text-[11px] text-slate-500">Manage branches across areas and divisions</p>
            </div>
            <a href="<?php echo e(route('shakhas.create')); ?>" class="inline-flex items-center gap-1 rounded-lg bg-navy-900 px-2.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                <span class="text-[13px] leading-none">+</span>
                Add Shakha
            </a>
        </div>

        <?php if(session('status')): ?>
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-4 py-2.5">Shakha Name</th>
                            <th class="px-4 py-2.5">Area</th>
                            <th class="px-4 py-2.5">Division</th>
                            <th class="px-4 py-2.5">Code</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5">Added On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $shakhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shakha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="text-[12px]">
                                <td class="px-4 py-2.5 font-medium text-navy-900"><?php echo e($shakha->name); ?></td>
                                <td class="px-4 py-2.5 text-slate-600"><?php echo e($shakha->area->name); ?></td>
                                <td class="px-4 py-2.5 text-slate-600"><?php echo e($shakha->area->division); ?></td>
                                <td class="px-4 py-2.5 text-slate-500"><?php echo e($shakha->code ?: '—'); ?></td>
                                <td class="px-4 py-2.5">
                                    <?php if($shakha->isActive()): ?>
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-600">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5 text-slate-500"><?php echo e($shakha->created_at->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-[12px] text-slate-400">
                                    No shakhas yet.
                                    <a href="<?php echo e(route('shakhas.create')); ?>" class="font-medium text-brand-600 hover:underline">Add the first one</a>
                                </td>
                            </tr>
                        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/shakhas/index.blade.php ENDPATH**/ ?>