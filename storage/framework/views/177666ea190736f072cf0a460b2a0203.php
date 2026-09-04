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
            <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">Add Project</h1>
            <p class="mt-0.5 text-[11px] text-slate-500">Creates master data used by Annual Audit project tabs</p>
        </div>

        <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-card">
            <div class="border-b border-slate-100 px-4 py-3.5">
                <p class="text-[13px] font-medium text-navy-900">Project details</p>
                <p class="mt-0.5 text-[11px] text-slate-500">Flags control which report tabs this project appears in</p>
            </div>

            <form
                method="POST"
                action="<?php echo e(route('projects.store')); ?>"
                class="px-4 py-4"
                x-data="{
                    locations: [{ name: '', division: '', status: 'active' }],
                    isPksf: <?php echo e(old('is_pksf') ? 'true' : 'false'); ?>,
                    isMaternity: <?php echo e(old('is_maternity') ? 'true' : 'false'); ?>,
                    hasAudit: <?php echo e(old('has_project_audit', true) ? 'true' : 'false'); ?>,
                    hasMonitoring: <?php echo e(old('has_project_monitoring', true) ? 'true' : 'false'); ?>,
                    get isSpecial() { return this.isPksf || this.isMaternity },
                    onSpecialChange() {
                        if (this.isSpecial) {
                            this.hasAudit = false;
                            this.hasMonitoring = false;
                        } else if (! this.hasAudit && ! this.hasMonitoring) {
                            this.hasAudit = true;
                            this.hasMonitoring = true;
                        }
                    },
                }"
            >
                <?php echo csrf_field(); ?>
                <div class="space-y-3">
                    <div>
                        <label for="name" class="mb-1 block text-[11px] font-medium text-slate-600">Project name</label>
                        <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'name','name' => 'name','type' => 'text','class' => 'block w-full rounded-lg text-[13px]','value' => old('name'),'required' => true,'placeholder' => 'e.g. Livelihood Resilience']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'name','name' => 'name','type' => 'text','class' => 'block w-full rounded-lg text-[13px]','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('name')),'required' => true,'placeholder' => 'e.g. Livelihood Resilience']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('name'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('name')),'class' => 'mt-1']); ?>
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
                        <label for="donor" class="mb-1 block text-[11px] font-medium text-slate-600">Donor (optional)</label>
                        <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['id' => 'donor','name' => 'donor','type' => 'text','class' => 'block w-full rounded-lg text-[13px]','value' => old('donor'),'placeholder' => 'e.g. PKSF / Donor A']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'donor','name' => 'donor','type' => 'text','class' => 'block w-full rounded-lg text-[13px]','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('donor')),'placeholder' => 'e.g. PKSF / Donor A']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('donor'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('donor')),'class' => 'mt-1']); ?>
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
                        <label for="status" class="mb-1 block text-[11px] font-medium text-slate-600">Status</label>
                        <select id="status" name="status" required class="block w-full rounded-lg border-slate-200 text-[13px] text-slate-800 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="active" <?php if(old('status', 'active') === 'active'): echo 'selected'; endif; ?>>Active</option>
                            <option value="inactive" <?php if(old('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <p class="mb-1.5 text-[11px] font-medium text-slate-600">Where this project appears in Annual Audit</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700">
                                <input type="checkbox" name="is_pksf" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" x-model="isPksf" @change="onSpecialChange()">
                                PKSF tab
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700">
                                <input type="checkbox" name="is_maternity" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" x-model="isMaternity" @change="onSpecialChange()">
                                Maternity (PKSF tab)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700" :class="isSpecial && 'opacity-60'">
                                <input type="checkbox" name="has_project_audit" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" x-model="hasAudit">
                                Project Audit tab
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-[12px] text-slate-700" :class="isSpecial && 'opacity-60'">
                                <input type="checkbox" name="has_project_monitoring" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" x-model="hasMonitoring">
                                Project Monitoring tab
                            </label>
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-400" x-show="isSpecial" x-cloak>PKSF / Maternity projects go to the PKSF &amp; Maternity work plan by default.</p>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <p class="text-[12px] font-medium text-navy-900">Locations</p>
                                <p class="text-[11px] text-slate-500">Schedules are generated per location</p>
                            </div>
                            <button type="button" @click="locations.push({ name: '', division: '', status: 'active' })" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-medium text-slate-600 hover:bg-slate-50">
                                + Location
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(loc, index) in locations" :key="index">
                                <div class="grid gap-2 rounded-lg border border-slate-100 p-2.5 sm:grid-cols-12">
                                    <div class="sm:col-span-4">
                                        <select :name="'locations['+index+'][division]'" x-model="loc.division" required class="block w-full rounded-lg border-slate-200 text-[12px]">
                                            <option value="">Select division</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($division); ?>"><?php echo e($division); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-5">
                                        <input type="text" :name="'locations['+index+'][name]'" x-model="loc.name" placeholder="Location / site name" class="block w-full rounded-lg border-slate-200 text-[12px]" required>
                                    </div>
                                    <div class="flex items-center gap-2 sm:col-span-3">
                                        <select :name="'locations['+index+'][status]'" x-model="loc.status" class="block w-full rounded-lg border-slate-200 text-[12px]">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <button type="button" @click="if (locations.length > 1) locations.splice(index, 1)" class="shrink-0 text-[11px] text-rose-500 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('locations'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('locations')),'class' => 'mt-1']); ?>
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
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3.5">
                    <a href="<?php echo e(route('projects.index')); ?>" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800">
                        Save Project
                    </button>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\projects\create.blade.php ENDPATH**/ ?>