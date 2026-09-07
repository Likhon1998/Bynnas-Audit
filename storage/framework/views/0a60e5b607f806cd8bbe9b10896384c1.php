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

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4">
            <a href="<?php echo e(route('monthly-visits.index', ['fy' => $assignment->workItem->fy_label, 'month' => $assignment->workItem->month_index])); ?>" class="text-[11px] font-medium text-brand-600 hover:underline">← Back</a>
            <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Reschedule visit</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                <?php echo e($assignment->workItem?->entity_label); ?> · Working days auto-calculated (Fri/Sat &amp; holidays excluded unless special request).
            </p>
            <p class="mt-1 text-[12px] text-slate-600">
                Current: <?php echo e($assignment->visitDateRangeLabel()); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->original_start_date): ?>
                    · First planned: <?php echo e($assignment->original_start_date->format('d M')); ?>–<?php echo e($assignment->original_end_date?->format('d M Y')); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700"><?php echo e($errors->first()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <form method="POST" action="<?php echo e(route('monthly-visits.reschedule.store', $assignment)); ?>" class="space-y-3 px-4 py-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Visitor(s) — one or more</label>
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/50 p-2">
                        <?php $selected = collect(old('employee_ids', $assignment->visitorList()->pluck('id')->all()))->map(fn ($id) => (int) $id); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-[12px] hover:bg-white">
                                <input
                                    type="checkbox"
                                    name="employee_ids[]"
                                    value="<?php echo e($employee->id); ?>"
                                    class="rounded border-slate-300 text-emerald-600"
                                    <?php if($selected->contains((int) $employee->id)): echo 'checked'; endif; ?>
                                >
                                <span class="font-medium text-navy-900"><?php echo e($employee->name); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->position): ?>
                                    <span class="text-slate-400">— <?php echo e($employee->position->title); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">New start date</label>
                        <input type="date" name="start_date" required value="<?php echo e(old('start_date', $assignment->start_date?->toDateString())); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">New end date</label>
                        <input type="date" name="end_date" required value="<?php echo e(old('end_date', $assignment->end_date?->toDateString())); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2 text-[12px] text-amber-950">
                    <input type="checkbox" name="count_off_days" value="1" class="mt-0.5 rounded border-amber-300 text-amber-600" <?php if(old('count_off_days', $assignment->count_off_days)): echo 'checked'; endif; ?>>
                    <span>Special request — count Fri/Sat &amp; holidays as working days</span>
                </label>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Reschedule reason (required)</label>
                    <textarea name="reschedule_reason" required rows="2" class="block w-full rounded-lg border-slate-200 text-[13px]"><?php echo e(old('reschedule_reason')); ?></textarea>
                </div>
                <p class="text-[11px] text-slate-500">Same person cannot be scheduled at two places on overlapping dates — conflicts are blocked.</p>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save reschedule</button>
                </div>
            </form>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\monthly-visits\reschedule.blade.php ENDPATH**/ ?>