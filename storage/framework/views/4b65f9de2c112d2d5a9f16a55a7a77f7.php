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
        $val = fn (string $key, $default = 0) => old($key, $existing?->{$key} ?? $default);
        $priorJune = 'June-'.$fy->startYear();
    ?>

    <div class="px-4 py-5 lg:px-6">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="<?php echo e(route('kpis.index', ['fy' => $fyLabel])); ?>" class="hover:text-brand-600">Annual KPI</a>
                    <span>/</span>
                    <span class="text-slate-600"><?php echo e($existing ? 'Edit' : 'Enter'); ?></span>
                </div>
                <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900"><?php echo e($shakha->name); ?></h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    <?php echo e($shakha->area?->name); ?> · FY <?php echo e($fyLabel); ?>

                    · Raw figures only (ratios calculated on Excel export)
                </p>
            </div>
            <a href="<?php echo e(route('kpis.index', ['fy' => $fyLabel])); ?>" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">Back to list</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700"><?php echo e($errors->first()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('kpis.store', $shakha)); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="fy_label" value="<?php echo e($fyLabel); ?>">

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Branch info</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-medium text-slate-600">Opening date</label>
                        <input type="date" name="opening_date" value="<?php echo e(old('opening_date', optional($shakha->opening_date ?? $shakha->opened_at)->format('Y-m-d'))); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-medium text-slate-600">Focal person name</label>
                        <input type="text" name="focal_person_name" value="<?php echo e(old('focal_person_name', $shakha->focal_person_name)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]" placeholder="e.g. Md. Rafiqul Islam">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Snapshot</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Month-end / year-end stock figures</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                        'fo_count' => 'FO #',
                        'total_samities' => 'Total Samities',
                        'total_members' => 'Total Members',
                        'total_borrowers' => 'Total Borrowers',
                        'total_od_borrowers' => 'Total OD Borrowers',
                        'savings_balance' => 'Savings Balance (Tk)',
                        'loan_outstanding' => 'Loan Outstanding (Tk)',
                        'recoverable' => 'Recoverable (Tk)',
                        'current_recovery' => 'Current Recovery (Tk)',
                        'due_recovery' => 'Due Recovery (Tk)',
                        'total_od_taka' => 'Total OD Taka',
                        'due_loanee_loan_outstanding' => 'Due Loanee Loan Outstanding (Tk)',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600"><?php echo e($label); ?></label>
                            <input type="number" name="<?php echo e($name); ?>" min="0" step="<?php echo e(str_contains($name, 'count') || str_contains($name, 'samities') || str_contains($name, 'members') || str_contains($name, 'borrowers') ? '1' : '0.01'); ?>" required value="<?php echo e($val($name)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Fiscal year activity</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Yellow columns in your Excel — increases are calculated automatically</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                        'fy_savings_collection' => 'FY Savings Collection',
                        'fy_savings_withdrawal' => 'FY Savings Withdrawal',
                        'fy_members_admission' => 'FY Members Admission',
                        'fy_members_dropout' => 'FY Members Dropout',
                        'fy_disbursement_borrowers' => 'FY Disbursement Borrowers',
                        'fy_fully_repayment_borrowers' => 'FY Fully Repayment Borrowers',
                        'fy_disbursement_amount' => 'FY Disbursement Amount',
                        'fy_loan_recovery' => 'FY Loan Recovery',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600"><?php echo e($label); ?></label>
                            <input type="number" name="<?php echo e($name); ?>" min="0" step="<?php echo e(str_contains($name, 'borrowers') || str_contains($name, 'admission') || str_contains($name, 'dropout') ? '1' : '0.01'); ?>" required value="<?php echo e($val($name)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white shadow-card">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <p class="text-[13px] font-semibold text-navy-900">Funds & dues</p>
                </div>
                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                        'own_fund_until_prior_june' => "Own Fund Until {$priorJune}",
                        'surplus_deficit_fy' => "Surplus/Deficit (FY {$fyLabel})",
                        'new_due' => 'New Due',
                        'due_increase_this_month' => 'Due Increase This Month',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-medium text-slate-600"><?php echo e($label); ?></label>
                            <input type="number" name="<?php echo e($name); ?>" step="0.01" required value="<?php echo e($val($name)); ?>" class="block w-full rounded-lg border-slate-200 text-[13px]">
                            <p class="mt-1 text-[10px] text-slate-400">Negative values allowed where applicable</p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-card">
                <p class="text-[11px] text-slate-500">After saving you return to the KPI list. Use <strong>Export Excel</strong> when ready for all branches.</p>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('kpis.index', ['fy' => $fyLabel])); ?>" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-navy-900 px-4 py-1.5 text-[12px] font-semibold text-white hover:bg-navy-800">Save KPI</button>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/kpis/edit.blade.php ENDPATH**/ ?>