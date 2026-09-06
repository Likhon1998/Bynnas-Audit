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

    <?php
        $isEdit = $employee !== null;
        $formEmployee = $employee;
        $currentPhotoUrl = $formEmployee?->photoUrl();
    ?>

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="mb-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="<?php echo e(route('shakha-employees.index')); ?>" class="hover:text-brand-600">Shakha Employees</a>
                    <span>/</span>
                    <span class="text-slate-600"><?php echo e($shakha->name); ?></span>
                </div>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">
                    <?php echo e($isEdit ? 'Edit employee' : 'Shakha staff'); ?>

                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    <?php echo e($shakha->area?->name ?: '—'); ?> · <?php echo e($shakha->code ?: 'No code'); ?> · <?php echo e($employees->count()); ?> on roster
                </p>
            </div>
            <a href="<?php echo e(route('shakha-employees.index', ['area_id' => $shakha->area_id, 'shakha_id' => $shakha->id])); ?>" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                Back to list
            </a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-[12px] text-emerald-700"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-3.5 py-2.5">
                    <p class="text-[12px] font-semibold text-navy-900">Roster</p>
                    <p class="text-[10px] text-slate-500">Photo · employee ID · name · designation</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-slate-100 bg-slate-50/80">
                            <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-3.5 py-2.5">Photo</th>
                                <th class="px-3.5 py-2.5">ID</th>
                                <th class="px-3.5 py-2.5">Name</th>
                                <th class="px-3.5 py-2.5">Designation</th>
                                <th class="px-3.5 py-2.5">Joined shakha</th>
                                <th class="px-3.5 py-2.5">Status</th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                                    <th class="px-3.5 py-2.5"></th>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="text-[12px] <?php echo e($isEdit && $formEmployee->id === $row->id ? 'bg-sky-50/60' : ''); ?>">
                                    <td class="px-3.5 py-2.5">
                                        <?php echo $__env->make('shakha-employees.partials.photo', ['employee' => $row, 'size' => 'sm'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </td>
                                    <td class="px-3.5 py-2.5 font-semibold text-navy-900"><?php echo e($row->employee_code); ?></td>
                                    <td class="px-3.5 py-2.5">
                                        <p class="font-medium text-slate-800"><?php echo e($row->name); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->phone || $row->email): ?>
                                            <p class="text-[10px] text-slate-400">
                                                <?php echo e(collect([$row->phone, $row->email])->filter()->implode(' · ')); ?>

                                            </p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-3.5 py-2.5 text-slate-600"><?php echo e($row->designation); ?></td>
                                    <td class="px-3.5 py-2.5 text-slate-500">
                                        <?php echo e($row->joined_shakha_at?->format('d M Y') ?: '—'); ?>

                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->isActive()): ?>
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">Active</span>
                                        <?php else: ?>
                                            <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-600">Inactive</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                                        <td class="px-3.5 py-2.5 text-right">
                                            <a href="<?php echo e(route('shakha-employees.edit', $row)); ?>" class="text-[11px] font-semibold text-[#2b579a] hover:underline">Edit</a>
                                            <form method="POST" action="<?php echo e(route('shakha-employees.destroy', $row)); ?>" class="ml-2 inline" onsubmit="return confirm('Remove this employee from the roster?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-[11px] font-semibold text-rose-600 hover:underline">Remove</button>
                                            </form>
                                        </td>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="<?php echo e($canManage ? 7 : 6); ?>" class="px-3.5 py-10 text-center text-[12px] text-slate-400">
                                        No employees on this shakha yet.
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
                <div
                    class="rounded-xl border border-slate-100 bg-white shadow-card"
                    x-data="{
                        preview: <?php echo \Illuminate\Support\Js::from($currentPhotoUrl)->toHtml() ?>,
                        onFile(event) {
                            const file = event.target.files?.[0];
                            if (!file) return;
                            this.preview = URL.createObjectURL(file);
                        }
                    }"
                >
                    <div class="border-b border-slate-100 px-3.5 py-2.5">
                        <p class="text-[12px] font-semibold text-navy-900"><?php echo e($isEdit ? 'Edit employee' : 'Add employee'); ?></p>
                        <p class="text-[10px] text-slate-500">Required: photo preferred · ID, name, designation</p>
                    </div>
                    <form
                        method="POST"
                        action="<?php echo e($isEdit ? route('shakha-employees.update', $formEmployee) : route('shakha-employees.store', $shakha)); ?>"
                        enctype="multipart/form-data"
                        class="space-y-3 px-3.5 py-3.5"
                    >
                        <?php echo csrf_field(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEdit): ?>
                            <?php echo method_field('PUT'); ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 p-3">
                            <label class="mb-2 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Employee photo</label>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <template x-if="preview">
                                        <img :src="preview" alt="Preview" class="h-16 w-16 rounded-full object-cover ring-1 ring-slate-200">
                                    </template>
                                    <template x-if="!preview">
                                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-white text-[14px] font-semibold text-slate-400 ring-1 ring-slate-200">
                                            <?php echo e($isEdit ? mb_strtoupper(mb_substr((string) $formEmployee->name, 0, 1)) : '?'); ?>

                                        </span>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <input
                                        type="file"
                                        name="photo"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="block w-full text-[11px] text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-navy-900 file:px-2.5 file:py-1.5 file:text-[11px] file:font-semibold file:text-white hover:file:bg-navy-800"
                                        @change="onFile($event)"
                                    >
                                    <p class="mt-1 text-[10px] text-slate-400">JPG, PNG or WebP · max 2 MB</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEdit && $currentPhotoUrl): ?>
                                        <label class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] text-rose-600">
                                            <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                            Remove current photo
                                        </label>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('photo'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('photo')),'class' => 'mt-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                        </div>

                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Employee ID</label>
                            <input type="text" name="employee_code" value="<?php echo e(old('employee_code', $formEmployee?->employee_code)); ?>" required class="h-8 w-full rounded-lg border-slate-200 text-[12px]" placeholder="e.g. EMP-001">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Employee name</label>
                            <input type="text" name="name" value="<?php echo e(old('name', $formEmployee?->name)); ?>" required class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Designation</label>
                            <input type="text" name="designation" value="<?php echo e(old('designation', $formEmployee?->designation)); ?>" required class="h-8 w-full rounded-lg border-slate-200 text-[12px]" placeholder="e.g. Branch Manager">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Phone</label>
                                <input type="text" name="phone" value="<?php echo e(old('phone', $formEmployee?->phone)); ?>" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status</label>
                                <select name="status" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                                    <option value="active" <?php if(old('status', $formEmployee?->status ?? 'active') === 'active'): echo 'selected'; endif; ?>>Active</option>
                                    <option value="inactive" <?php if(old('status', $formEmployee?->status ?? 'active') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email', $formEmployee?->email)); ?>" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Joined org</label>
                                <input type="date" name="joined_organization_at" value="<?php echo e(old('joined_organization_at', $formEmployee?->joined_organization_at?->format('Y-m-d'))); ?>" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Joined shakha</label>
                                <input type="date" name="joined_shakha_at" value="<?php echo e(old('joined_shakha_at', $formEmployee?->joined_shakha_at?->format('Y-m-d'))); ?>" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Sort order</label>
                            <input type="number" min="0" name="sort_order" value="<?php echo e(old('sort_order', $formEmployee?->sort_order ?? 0)); ?>" class="h-8 w-full rounded-lg border-slate-200 text-[12px]">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Notes</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-200 text-[12px]"><?php echo e(old('notes', $formEmployee?->notes)); ?></textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-medium text-white hover:bg-navy-800">
                                <?php echo e($isEdit ? 'Save changes' : 'Add employee'); ?>

                            </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEdit): ?>
                                <a href="<?php echo e(route('shakha-employees.manage', $shakha)); ?>" class="text-[11px] font-semibold text-slate-500 hover:underline">Cancel</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-4 text-[12px] text-slate-500">
                    You can view this roster. Ask someone with shakha manage access to add or edit employees.
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/shakha-employees/manage.blade.php ENDPATH**/ ?>