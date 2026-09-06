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
        $oldLocations = old('locations');
        $initialLocations = is_array($oldLocations) && count($oldLocations)
            ? collect($oldLocations)->map(fn ($loc) => [
                'name' => (string) ($loc['name'] ?? ''),
                'division' => (string) ($loc['division'] ?? ''),
                'status' => (string) ($loc['status'] ?? 'active'),
            ])->values()->all()
            : [['name' => '', 'division' => '', 'status' => 'active']];
    ?>

    <div class="px-4 py-3 lg:px-6">
        <form
            method="POST"
            action="<?php echo e(route('projects.store')); ?>"
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            x-data="{
                locations: <?php echo e(Js::from($initialLocations)); ?>,
                isPksf: <?php echo e(old('is_pksf') ? 'true' : 'false'); ?>,
                isMaternity: <?php echo e(old('is_maternity') ? 'true' : 'false'); ?>,
                hasAudit: <?php echo e(old('has_project_audit', true) ? 'true' : 'false'); ?>,
                hasMonitoring: <?php echo e(old('has_project_monitoring', true) ? 'true' : 'false'); ?>,
                get isSpecial() { return this.isPksf || this.isMaternity },
                onSpecialChange() {
                    if (this.isSpecial) {
                        this.hasAudit = false
                        this.hasMonitoring = false
                    } else if (! this.hasAudit && ! this.hasMonitoring) {
                        this.hasAudit = true
                        this.hasMonitoring = true
                    }
                },
                addLocation() {
                    this.locations.push({ name: '', division: '', status: 'active' })
                },
                removeLocation(index) {
                    if (this.locations.length > 1) this.locations.splice(index, 1)
                },
            }"
        >
            <?php echo csrf_field(); ?>

            
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50/70 px-3 py-2.5 sm:px-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <a href="<?php echo e(route('projects.index')); ?>" class="text-[11px] font-medium text-[#2b579a] hover:underline">Projects</a>
                        <span class="text-[11px] text-slate-300">/</span>
                        <h1 class="text-[14px] font-semibold tracking-tight text-navy-900">Add project</h1>
                    </div>
                    <p class="mt-0.5 text-[11px] text-slate-500">Master data for Annual Audit tabs &amp; schedules</p>
                </div>
                <div class="flex shrink-0 items-center gap-1.5">
                    <a href="<?php echo e(route('projects.index')); ?>" class="inline-flex h-8 items-center rounded-md px-2.5 text-[12px] font-medium text-slate-600 hover:bg-slate-200/60">Cancel</a>
                    <button type="submit" class="inline-flex h-8 items-center rounded-md bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">
                        Save project
                    </button>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="border-b border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-800 sm:px-4">
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="grid gap-x-4 gap-y-3 border-b border-slate-100 px-3 py-3 sm:px-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label for="name" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Project name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="<?php echo e(old('name')); ?>"
                        required
                        placeholder="e.g. Livelihood Resilience"
                        class="block w-full rounded-md border-slate-200 py-1.5 text-[13px] leading-5"
                    >
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('name'),'class' => 'mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('name')),'class' => 'mt-0.5']); ?>
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
                <div class="lg:col-span-4">
                    <label for="donor" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Donor</label>
                    <input
                        id="donor"
                        name="donor"
                        type="text"
                        value="<?php echo e(old('donor')); ?>"
                        placeholder="Optional"
                        class="block w-full rounded-md border-slate-200 py-1.5 text-[13px] leading-5"
                    >
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('donor'),'class' => 'mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('donor')),'class' => 'mt-0.5']); ?>
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
                <div class="lg:col-span-3">
                    <label for="status" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Status</label>
                    <select id="status" name="status" required class="block w-full rounded-md border-slate-200 py-1.5 text-[13px] leading-5 text-slate-800">
                        <option value="active" <?php if(old('status', 'active') === 'active'): echo 'selected'; endif; ?>>Active</option>
                        <option value="inactive" <?php if(old('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                    </select>
                </div>

                <div class="lg:col-span-12">
                    <div class="mb-1.5 flex flex-wrap items-baseline justify-between gap-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Annual Audit tabs</p>
                        <p class="text-[10px] text-slate-400">PKSF / Maternity replace standard tabs</p>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-4">
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-md border px-2.5 py-1.5 text-[12px] transition"
                            :class="isPksf ? 'border-sky-400 bg-sky-50 font-semibold text-sky-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            <input type="checkbox" name="is_pksf" value="1" class="rounded border-slate-300 text-sky-600" x-model="isPksf" @change="onSpecialChange()">
                            PKSF
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-md border px-2.5 py-1.5 text-[12px] transition"
                            :class="isMaternity ? 'border-violet-400 bg-violet-50 font-semibold text-violet-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            <input type="checkbox" name="is_maternity" value="1" class="rounded border-slate-300 text-violet-600" x-model="isMaternity" @change="onSpecialChange()">
                            Maternity
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-md border px-2.5 py-1.5 text-[12px] transition"
                            :class="[
                                isSpecial ? 'pointer-events-none opacity-40' : '',
                                hasAudit && !isSpecial ? 'border-emerald-400 bg-emerald-50 font-semibold text-emerald-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'
                            ]"
                        >
                            <input type="checkbox" name="has_project_audit" value="1" class="rounded border-slate-300 text-emerald-600" x-model="hasAudit" :disabled="isSpecial">
                            Project Audit
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-md border px-2.5 py-1.5 text-[12px] transition"
                            :class="[
                                isSpecial ? 'pointer-events-none opacity-40' : '',
                                hasMonitoring && !isSpecial ? 'border-amber-400 bg-amber-50 font-semibold text-amber-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'
                            ]"
                        >
                            <input type="checkbox" name="has_project_monitoring" value="1" class="rounded border-slate-300 text-amber-600" x-model="hasMonitoring" :disabled="isSpecial">
                            Monitoring
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-sky-700" x-show="isSpecial" x-cloak>Schedules go to the PKSF &amp; Maternity work plan.</p>
                </div>
            </div>

            
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/50 px-3 py-2 sm:px-4">
                <div class="flex items-center gap-2">
                    <p class="text-[12px] font-semibold text-navy-900">Locations</p>
                    <span class="rounded bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-semibold tabular-nums text-slate-600" x-text="locations.length"></span>
                    <p class="hidden text-[11px] text-slate-400 sm:inline">Each site becomes a schedule row on generate / sync</p>
                </div>
                <button
                    type="button"
                    @click="addLocation()"
                    class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50"
                >+ Location</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="border-b border-slate-100 bg-white">
                        <tr class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <th class="w-10 px-3 py-1.5 sm:px-4">#</th>
                            <th class="px-2 py-1.5">Division</th>
                            <th class="px-2 py-1.5">Location / site</th>
                            <th class="w-28 px-2 py-1.5">Status</th>
                            <th class="w-16 px-2 py-1.5 sm:px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(loc, index) in locations" :key="index">
                            <tr class="bg-white hover:bg-slate-50/80">
                                <td class="px-3 py-2 align-middle text-[11px] tabular-nums text-slate-400 sm:px-4" x-text="index + 1"></td>
                                <td class="px-2 py-2 align-middle">
                                    <select :name="'locations['+index+'][division]'" x-model="loc.division" required class="block w-full min-w-[9rem] rounded-md border-slate-200 py-1.5 text-[12px] leading-5">
                                        <option value="">Division…</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($division); ?>"><?php echo e($division); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </td>
                                <td class="px-2 py-2 align-middle">
                                    <input
                                        type="text"
                                        :name="'locations['+index+'][name]'"
                                        x-model="loc.name"
                                        placeholder="Site name"
                                        required
                                        class="block w-full min-w-[10rem] rounded-md border-slate-200 py-1.5 text-[12px] leading-5"
                                    >
                                </td>
                                <td class="px-2 py-2 align-middle">
                                    <select :name="'locations['+index+'][status]'" x-model="loc.status" class="block w-full rounded-md border-slate-200 py-1.5 text-[12px] leading-5">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </td>
                                <td class="px-2 py-2 align-middle text-right sm:px-4">
                                    <button
                                        type="button"
                                        @click="removeLocation(index)"
                                        class="inline-flex items-center text-[11px] font-medium leading-5 text-rose-600 hover:underline disabled:cursor-not-allowed disabled:opacity-25"
                                        :disabled="locations.length <= 1"
                                    >Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 bg-slate-50/70 px-3 py-2.5 sm:px-4">
                <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('locations')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('locations'))]); ?>
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
                <div class="ml-auto flex items-center gap-1.5">
                    <a href="<?php echo e(route('projects.index')); ?>" class="inline-flex h-8 items-center rounded-md px-2.5 text-[12px] font-medium text-slate-600 hover:bg-slate-200/60">Cancel</a>
                    <button type="submit" class="inline-flex h-8 items-center rounded-md bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">
                        Save project
                    </button>
                </div>
            </div>
        </form>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/projects/create.blade.php ENDPATH**/ ?>