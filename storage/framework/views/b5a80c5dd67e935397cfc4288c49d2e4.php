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

    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                    <a href="<?php echo e(route('shakhas.index')); ?>" class="hover:text-brand-600">All Shakha</a>
                    <span>/</span>
                    <span class="text-slate-600">Risk Branch Analysis</span>
                </div>
                <h1 class="mt-1 text-[15px] font-semibold tracking-tight text-navy-900"><?php echo e($shakha->name); ?></h1>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    <?php echo e($shakha->area?->name); ?> · <?php echo e($shakha->area?->division); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shakha->code): ?>
                        · <?php echo e($shakha->code); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>
            <a href="<?php echo e(route('shakhas.index')); ?>" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50">
                Back to list
            </a>
        </div>

        <div class="mb-4 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-[12px] text-sky-900">
            <p class="font-semibold">Auto-mapped from annual KPI</p>
            <p class="mt-0.5 text-sky-800">
                Overdue principal = KPI Total OD Taka. Profitability uses KPI Surplus/Deficit.
                Total income and total expenditure are entered manually for OSS.
            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($kpi)): ?>
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[12px] text-amber-950">
                <p class="font-semibold">Annual KPI is required first</p>
                <p class="mt-0.5 text-amber-900">
                    There is no KPI for <span class="font-medium"><?php echo e($shakha->name); ?></span> in FY <?php echo e($fy->label); ?>.
                    Enter the annual KPI, then return here to run risk analysis.
                </p>
                <a href="<?php echo e(route('kpis.edit', ['shakha' => $shakha, 'fy' => $fy->label])); ?>" class="mt-2 inline-flex rounded-lg bg-amber-700 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-amber-800">
                    Enter KPI for FY <?php echo e($fy->label); ?> →
                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="mb-3 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[12px] text-rose-700">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="GET" action="<?php echo e(route('shakhas.risk.create', $shakha)); ?>" class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-white p-4 shadow-card">
            <div>
                <label for="month" class="mb-1 block text-[11px] font-medium text-slate-600">Assessment month</label>
                <select id="month" name="month" class="h-9 rounded-lg border-slate-200 text-[13px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($m); ?>" <?php if($m === $month): echo 'selected'; endif; ?>><?php echo e(date('F', mktime(0, 0, 0, $m, 1))); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div>
                <label for="year" class="mb-1 block text-[11px] font-medium text-slate-600">Year</label>
                <select id="year" name="year" class="h-9 rounded-lg border-slate-200 text-[13px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($y = now()->year + 1; $y >= now()->year - 5; $y--): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($y); ?>" <?php if($y === $year): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <button type="submit" class="h-9 rounded-lg border border-slate-200 bg-slate-50 px-3 text-[12px] font-medium text-slate-700 hover:bg-slate-100">
                Load period
            </button>

            <div class="ml-auto flex flex-wrap items-center gap-2 text-[11px]">
                <span class="rounded-md bg-slate-100 px-2 py-1 font-medium text-slate-600">FY <?php echo e($fy->label); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kpi): ?>
                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700">Annual KPI found</span>
                    <span class="text-slate-500">OTR <?php echo e(number_format(($otr ?? 0) * 100, 2)); ?>%</span>
                    <span class="text-slate-500">DR/NPLR <?php echo e(number_format(($dr ?? 0) * 100, 2)); ?>%</span>
                    <span class="text-slate-500">Surplus <?php echo e(number_format((float) ($surplus ?? 0), 2)); ?></span>
                    <span class="text-slate-500">OD <?php echo e(number_format((float) ($totalOdTaka ?? 0), 2)); ?></span>
                <?php else: ?>
                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-800">
                        No annual KPI for FY <?php echo e($fy->label); ?>

                    </span>
                    <a href="<?php echo e(route('kpis.edit', $shakha)); ?>?fy=<?php echo e(urlencode($fy->label)); ?>" class="font-semibold text-brand-600 hover:underline">
                        Enter KPI →
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </form>

        <form
            method="POST"
            action="<?php echo e(route('shakhas.risk.store', $shakha)); ?>"
            class="rounded-2xl border border-slate-100 bg-white shadow-card"
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="assessment_month" value="<?php echo e($month); ?>">
            <input type="hidden" name="assessment_year" value="<?php echo e($year); ?>">

            <div class="border-b border-slate-100 px-5 py-3.5">
                <p class="text-[13px] font-semibold text-navy-900">Operational &amp; audit inputs</p>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Total OD Taka and Surplus/Deficit come from KPI. Enter income, expenditure, and other operational fields.
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existing): ?>
                        <span class="text-brand-600">Existing score: <?php echo e($existing->total_weighted_score); ?> · <?php echo e($existing->risk_category); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>

            <div
                class="grid gap-4 px-5 py-5 sm:grid-cols-2"
                x-data="{
                    income: <?php echo \Illuminate\Support\Js::from((float) old('total_income', $existing?->total_income ?? 0))->toHtml() ?>,
                    expenditure: <?php echo \Illuminate\Support\Js::from((float) old('total_expenditure', $existing?->total_expenditure ?? 0))->toHtml() ?>,
                    get ossPct() {
                        return this.expenditure > 0 ? (this.income / this.expenditure) * 100 : 0;
                    }
                }"
            >
                <div class="sm:col-span-2 grid gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3 sm:grid-cols-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Surplus/Deficit (KPI)</p>
                        <p class="mt-1 text-[13px] font-semibold tabular-nums text-navy-900"><?php echo e(number_format((float) ($surplus ?? 0), 2)); ?></p>
                        <p class="mt-0.5 text-[10px] text-slate-500"><?php echo e(((float) ($surplus ?? 0)) >= 0 ? 'Profit' : 'Loss'); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total OD Taka → overdue</p>
                        <p class="mt-1 text-[13px] font-semibold tabular-nums text-navy-900"><?php echo e(number_format((float) ($totalOdTaka ?? 0), 2)); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">NPLR / DR</p>
                        <p class="mt-1 text-[13px] font-semibold tabular-nums text-navy-900"><?php echo e(number_format(($nplr ?? 0) * 100, 2)); ?>%</p>
                    </div>
                </div>

                <div>
                    <label for="total_income" class="mb-1.5 block text-[11px] font-medium text-slate-600">Total income <span class="text-rose-500">*</span></label>
                    <input id="total_income" name="total_income" type="number" step="0.01" min="0" required
                        x-model.number="income"
                        value="<?php echo e(old('total_income', $existing?->total_income)); ?>"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-[10px] text-slate-400">Not on KPI — needed for OSS (with expenditure).</p>
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('total_income'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('total_income')),'class' => 'mt-1']); ?>
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
                    <label for="total_expenditure" class="mb-1.5 block text-[11px] font-medium text-slate-600">Total expenditure <span class="text-rose-500">*</span></label>
                    <input id="total_expenditure" name="total_expenditure" type="number" step="0.01" min="0" required
                        x-model.number="expenditure"
                        value="<?php echo e(old('total_expenditure', $existing?->total_expenditure)); ?>"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-[10px] text-slate-400">Not on KPI — enter manually. OSS: <span class="font-medium text-slate-600" x-text="ossPct.toFixed(2) + '%'"></span></p>
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('total_expenditure'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('total_expenditure')),'class' => 'mt-1']); ?>
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
                    <label for="write_off_principal_amount" class="mb-1.5 block text-[11px] font-medium text-slate-600">Write-off principal amount <span class="text-rose-500">*</span></label>
                    <input id="write_off_principal_amount" name="write_off_principal_amount" type="number" step="0.01" min="0" required
                        value="<?php echo e(old('write_off_principal_amount', $existing?->write_off_principal_amount)); ?>"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('write_off_principal_amount'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('write_off_principal_amount')),'class' => 'mt-1']); ?>
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
                    <label for="savings_adjustment_amount" class="mb-1.5 block text-[11px] font-medium text-slate-600">Savings adjustment amount <span class="text-rose-500">*</span></label>
                    <input id="savings_adjustment_amount" name="savings_adjustment_amount" type="number" step="0.01" min="0" required
                        value="<?php echo e(old('savings_adjustment_amount', $existing?->savings_adjustment_amount)); ?>"
                        class="block w-full rounded-lg border-slate-200 text-[13px] shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('savings_adjustment_amount'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('savings_adjustment_amount')),'class' => 'mt-1']); ?>
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

                <?php
                    $distanceYes = (bool) old(
                        'distance_from_area_office_km',
                        ($existing?->distance_from_area_office_km ?? 0) > 0
                    );
                ?>
                <div>
                    <p class="mb-1.5 block text-[11px] font-medium text-slate-600">More than 20 km from area office? <span class="text-rose-500">*</span></p>
                    <div class="flex gap-2">
                        <label class="inline-flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700">
                            <input type="radio" name="distance_from_area_office_km" value="1" class="text-brand-600 focus:ring-brand-500" <?php if($distanceYes): echo 'checked'; endif; ?>>
                            Yes
                        </label>
                        <label class="inline-flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700">
                            <input type="radio" name="distance_from_area_office_km" value="0" class="text-brand-600 focus:ring-brand-500" <?php if(! $distanceYes): echo 'checked'; endif; ?>>
                            No
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400">Yes adds risk points in the scoring matrix.</p>
                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('distance_from_area_office_km'),'class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('distance_from_area_office_km')),'class' => 'mt-1']); ?>
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

                <div class="sm:col-span-2 grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3 text-[12px] text-slate-700">
                        <input type="hidden" name="has_both_bm_and_abm" value="0">
                        <input
                            type="checkbox"
                            name="has_both_bm_and_abm"
                            value="1"
                            class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            <?php if(old('has_both_bm_and_abm', $existing?->has_both_bm_and_abm)): echo 'checked'; endif; ?>
                        >
                        <span>
                            <span class="font-medium text-navy-900">Has both BM and ABM</span>
                            <span class="mt-0.5 block text-[11px] text-slate-500">Unchecked adds risk points in the scoring matrix.</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3 text-[12px] text-slate-700">
                        <input type="hidden" name="special_audit_last_two_years" value="0">
                        <input
                            type="checkbox"
                            name="special_audit_last_two_years"
                            value="1"
                            class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            <?php if(old('special_audit_last_two_years', $existing?->special_audit_last_two_years)): echo 'checked'; endif; ?>
                        >
                        <span>
                            <span class="font-medium text-navy-900">Special audit in last two years</span>
                            <span class="mt-0.5 block text-[11px] text-slate-500">Used as an audit-coverage factor in scoring.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5">
                <p class="text-[11px] text-slate-400">Score bands: 0–25 Low · 26–45 Medium · 46–65 High · 66+ Significant</p>
                <div class="flex items-center gap-1.5">
                    <a href="<?php echo e(route('shakhas.index')); ?>" class="rounded-lg px-3 py-1.5 text-[12px] font-medium text-slate-500 hover:bg-white">Cancel</a>
                    <button
                        type="submit"
                        <?php if(! $kpi): echo 'disabled'; endif; ?>
                        class="inline-flex items-center rounded-lg bg-navy-900 px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-navy-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Calculate &amp; save risk
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\shakhas\risk\create.blade.php ENDPATH**/ ?>