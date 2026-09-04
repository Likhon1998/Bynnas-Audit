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
            <a href="<?php echo e(route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index])); ?>" class="text-[11px] font-medium text-brand-600 hover:underline">← Back to monthly worklist</a>
            <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900">Assign visitor</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">
                <?php echo e($item->entity_label); ?> · <?php echo e($item->activityType?->name); ?> · <?php echo e($fy->months()[$item->month_index]['label']); ?> <?php echo e($fy->months()[$item->month_index]['year']); ?>

            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('conflict_warning')): ?>
            <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">
                <p class="font-semibold"><?php echo e(session('conflict_warning')); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = session('conflicts', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <p class="mt-1 text-amber-800">• <?php echo e($c->employee?->name); ?>: <?php echo e($c->start_date?->format('d M')); ?>–<?php echo e($c->end_date?->format('d M')); ?> (<?php echo e($c->workItem?->entity_label); ?>)</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p class="mt-2 text-[11px]">Check “Override conflict” below to save anyway.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg bg-rose-50 px-3 py-2 text-[12px] text-rose-700"><?php echo e($errors->first()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="max-w-xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <form method="POST" action="<?php echo e(route('monthly-visits.assign.store', $item)); ?>" class="space-y-3 px-4 py-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Assigned person (Organogram)</label>
                    <select name="employee_id" required class="block w-full rounded-lg border-slate-200 text-[13px]">
                        <option value="">Select staff</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($employee->id); ?>" <?php if(old('employee_id') == $employee->id): echo 'selected'; endif; ?>>
                                <?php echo e($employee->name); ?><?php echo e($employee->position ? ' — '.$employee->position->title : ''); ?>

                            </option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start date</label>
                        <input type="date" name="start_date" required value="<?php echo e(old('start_date', $defaultStart)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">End date</label>
                        <input type="date" name="end_date" required value="<?php echo e(old('end_date', $defaultEnd)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Start time (optional)</label>
                        <input type="time" name="start_time" value="<?php echo e(old('start_time')); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">End time (optional)</label>
                        <input type="time" name="end_time" value="<?php echo e(old('end_time')); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Duration mode</label>
                        <select name="duration_mode" class="block w-full rounded-lg border-slate-200 text-[13px]">
                            <option value="calendar" <?php if(old('duration_mode', 'calendar') === 'calendar'): echo 'selected'; endif; ?>>Calendar days</option>
                            <option value="working" <?php if(old('duration_mode') === 'working'): echo 'selected'; endif; ?>>Working days</option>
                            <option value="manual" <?php if(old('duration_mode') === 'manual'): echo 'selected'; endif; ?>>Manual days</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-600">Manual duration (if manual)</label>
                        <input type="number" min="1" max="60" name="duration_days" value="<?php echo e(old('duration_days', 5)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Last audit / monitoring upto</label>
                    <input type="date" name="last_audit_upto" value="<?php echo e(old('last_audit_upto', $lastUpto)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    <p class="mt-1 text-[10px] text-slate-400">Pre-filled from history when available; override if needed.</p>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Purpose</label>
                    <input type="text" name="purpose" value="<?php echo e(old('purpose', $item->activityType?->name)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Remarks</label>
                    <textarea name="remarks" rows="2" class="block w-full rounded-lg border-slate-200 text-[13px]"><?php echo e(old('remarks')); ?></textarea>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('conflict_warning')): ?>
                    <label class="flex items-center gap-2 text-[12px] text-amber-800">
                        <input type="checkbox" name="override_conflict" value="1" class="rounded border-amber-300 text-amber-600">
                        Override conflict and assign anyway
                    </label>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?php echo e(route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index])); ?>" class="rounded-lg px-3 py-1.5 text-[12px] text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">Save assignment</button>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\monthly-visits\assign.blade.php ENDPATH**/ ?>