<?php
    use App\Livewire\MakeAuditReport;
    $editable = $editable ?? false;
    $compact = $compact ?? false;
    $tableClass = $compact ? 'a4-table a4-table-compact text-[8px]' : 'w-full border-collapse text-[10px]';
    $cellPad = $compact ? '' : 'border border-slate-800 px-1 py-0.5';
    $dash = $dash ?? '………………';
    $findings = $page13Findings ?? [];
    $samityFields = ['samity_no', 'member_name_id', 'date', 'savings', 'voluntary', 'term', 'installment', 'total_collection', 'deposit_date', 'deposit_amount', 'difference', 'staff_name_id'];
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $findings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fIndex => $finding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php
        $anchor = MakeAuditReport::findingAnchorId($finding['serial'] ?? '');
        $detailType = (string) ($finding['detail_type'] ?? 'none');
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anchor !== ''): ?>
        <a id="<?php echo e($anchor); ?>" name="<?php echo e($anchor); ?>"></a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="<?php echo e($compact ? 'a4-table a4-table-compact text-[9px]' : 'w-full border-collapse text-[10.5px]'); ?> mb-[2mm]">
        <tbody>
            <tr>
                <td class="<?php echo e($cellPad); ?> w-[9%] text-center font-bold align-top finding-serial-cell">
                    <?php echo $__env->make('livewire.partials.audit-finding-serial-cell', [
                        'editable' => $editable,
                        'wireModel' => $editable ? 'page13Findings.'.$fIndex.'.serial' : null,
                        'value' => $finding['serial'] ?? '',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
                <td class="<?php echo e($cellPad); ?> w-[11%] text-center font-bold align-top">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <input type="text" wire:model.live="page13Findings.<?php echo e($fIndex); ?>.title" class="w-full border-0 bg-sky-50/40 text-center font-bold">
                    <?php else: ?>
                        <?php echo e($finding['title'] ?? 'শিরোনাম'); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="<?php echo e($cellPad); ?> align-top">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <?php echo $__env->make('livewire.partials.audit-indicator-combobox', [
                            'index' => $fIndex,
                            'value' => $finding['body'] ?? '',
                            'indicators' => $indicatorOptions ?? $financialIndicatorOptions ?? [],
                            'collection' => 'page13Findings',
                            'wireKey' => 'p13-ind-'.$fIndex.'-'.md5((string) ($finding['body'] ?? '')),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                            <span class="font-semibold">টাকার পরিমাণ:</span>
                            <input type="text" wire:model.live="page13Findings.<?php echo e($fIndex); ?>.amount" class="inline-input min-w-[100px]">
                        </div>
                    <?php else: ?>
                        <p class="m-0 whitespace-pre-wrap text-justify leading-[1.45]"><?php echo e($finding['body'] ?? ''); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finding['amount'] ?? '') !== ''): ?>
                            <p class="mt-[1mm] m-0"><span class="font-semibold">টাকার পরিমাণ:</span> <?php echo e($finding['amount']); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="<?php echo e($cellPad); ?> w-[17%] p-0 align-top">
                    <?php echo $__env->make('livewire.partials.audit-rating-box', [
                        'rating' => $finding['rating'] ?? '',
                        'editable' => $editable,
                        'wireModel' => $editable ? 'page13Findings.'.$fIndex.'.rating' : null,
                        'findingRatings' => $findingRatings ?? [],
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="mb-[2mm]">
        <p class="mb-[1mm] font-bold">প্রচলিত নিয়ম (Criteria):</p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.criteria" rows="4" class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        <?php elseif(($finding['criteria'] ?? '') !== ''): ?>
            <p class="m-0 text-justify"><?php echo e($finding['criteria']); ?></p>
        <?php else: ?>
            <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mb-[2mm]">
        <p class="mb-[1mm] font-bold">পর্যবেক্ষণ (Observation) :</p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.observation" rows="3" class="w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
        <?php elseif(($finding['observation'] ?? '') !== ''): ?>
            <p class="m-0 text-justify"><?php echo e($finding['observation']); ?></p>
        <?php else: ?>
            <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'page13Findings.' . $fIndex . '.statsRows','columns' => ['total_population', 'sample_size', 'instances_found', 'percentage'],'hint' => 'Stats: Excel থেকে ৪ কলাম কপি করে পেস্ট করুন']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('page13Findings.' . $fIndex . '.statsRows'),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['total_population', 'sample_size', 'instances_found', 'percentage']),'hint' => 'Stats: Excel থেকে ৪ কলাম কপি করে পেস্ট করুন']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3931ccc341723360a2655698c41db1b9)): ?>
<?php $attributes = $__attributesOriginal3931ccc341723360a2655698c41db1b9; ?>
<?php unset($__attributesOriginal3931ccc341723360a2655698c41db1b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3931ccc341723360a2655698c41db1b9)): ?>
<?php $component = $__componentOriginal3931ccc341723360a2655698c41db1b9; ?>
<?php unset($__componentOriginal3931ccc341723360a2655698c41db1b9); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<table class="<?php echo e($compact ? 'a4-table a4-table-compact text-[9px]' : 'w-full border-collapse text-[10.5px]'); ?> mb-[2mm]">
        <?php echo $__env->make('livewire.partials.audit-stats-thead', [
            'editable' => $editable,
            'cellPad' => $cellPad,
            'variant' => 'stats',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($finding['statsRows'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['total_population', 'sample_size', 'instances_found', 'percentage']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <td class="<?php echo e($cellPad); ?> text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(((string) $field === 'date' || str_ends_with((string) $field, '_date') || preg_match('/^date[_\d]/', (string) $field))): ?>
                                        <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'page13Findings.'.e($fIndex).'.statsRows.'.e($rowIndex).'.'.e($field).'','format' => 'dmy','class' => 'w-full border-0 bg-transparent text-center text-[11px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'page13Findings.'.e($fIndex).'.statsRows.'.e($rowIndex).'.'.e($field).'','format' => 'dmy','class' => 'w-full border-0 bg-transparent text-center text-[11px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $attributes = $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $component = $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
                                    <?php else: ?>
                                        <input type="text" wire:model.live="page13Findings.<?php echo e($fIndex); ?>.statsRows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" class="w-full border-0 bg-transparent text-center text-[11px]">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <?php echo e($row[$field] ?? ''); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <td class="<?php echo e($cellPad); ?> text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($finding['statsRows'] ?? []) > 1): ?>
                                <button type="button" wire:click="removePage13StatsRow(<?php echo e($fIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[10px] text-rose-600">×</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <button type="button" wire:click="addPage13StatsRow(<?php echo e($fIndex); ?>)" class="mb-[2mm] text-[11px] font-medium text-[#2b579a]">+ Stats row</button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
        <div class="mb-[2mm] flex flex-wrap items-center gap-3">
            <label class="text-[11px] font-semibold text-slate-600">বিস্তারিত ধরন:</label>
            <select wire:model.live="page13Findings.<?php echo e($fIndex); ?>.detail_type" class="rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[11px]">
                <option value="samity_collection">সমিতি আদায় তালিকা</option>
                <option value="none">নেই</option>
            </select>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailType === 'samity_collection'): ?>
        <p class="mb-[1mm] font-semibold"><?php echo e($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:'); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <input type="text" wire:model.live="page13Findings.<?php echo e($fIndex); ?>.detail_intro" class="mb-[2mm] w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[11px]">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-[2mm] overflow-x-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <?php if (isset($component)) { $__componentOriginal3931ccc341723360a2655698c41db1b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3931ccc341723360a2655698c41db1b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-excel-paste-zone','data' => ['path' => 'page13Findings.' . $fIndex . '.samityRows','columns' => ['samity_no', 'member_name_id', 'date', 'savings', 'voluntary', 'term', 'installment', 'total_collection', 'deposit_date', 'deposit_amount', 'difference', 'staff_name_id'],'hint' => 'Samity paste']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-excel-paste-zone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('page13Findings.' . $fIndex . '.samityRows'),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['samity_no', 'member_name_id', 'date', 'savings', 'voluntary', 'term', 'installment', 'total_collection', 'deposit_date', 'deposit_amount', 'difference', 'staff_name_id']),'hint' => 'Samity paste']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3931ccc341723360a2655698c41db1b9)): ?>
<?php $attributes = $__attributesOriginal3931ccc341723360a2655698c41db1b9; ?>
<?php unset($__attributesOriginal3931ccc341723360a2655698c41db1b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3931ccc341723360a2655698c41db1b9)): ?>
<?php $component = $__componentOriginal3931ccc341723360a2655698c41db1b9; ?>
<?php unset($__componentOriginal3931ccc341723360a2655698c41db1b9); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php
                    $hSamityR1 = $tableHeaders['samity_r1'] ?? \App\Support\AuditTableHeaders::defaults()['samity_r1'];
                    $hSamityR2 = $tableHeaders['samity_r2'] ?? \App\Support\AuditTableHeaders::defaults()['samity_r2'];
                ?>
<table class="<?php echo e($tableClass); ?> min-w-full">
                <thead>
                    <tr class="bg-slate-100">
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.0','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.0','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[0]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.1','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.1','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[1]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.2','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.2','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[2]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.3','class' => ''.e($cellPad).' font-semibold text-center','colspan' => '5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.3','class' => ''.e($cellPad).' font-semibold text-center','colspan' => '5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[3]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.4','class' => ''.e($cellPad).' font-semibold text-center','colspan' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.4','class' => ''.e($cellPad).' font-semibold text-center','colspan' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[4]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.5','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.5','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[5]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r1.6','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => 'tableHeaders.samity_r1.6','class' => ''.e($cellPad).' font-semibold text-center','rowspan' => '2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($hSamityR1[6]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <th class="<?php echo e($cellPad); ?>" rowspan="2"></th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                    <tr class="bg-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hSamityR2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hi => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.samity_r2.'.$hi,'class' => ''.e($cellPad).' font-semibold text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('tableHeaders.samity_r2.'.$hi),'class' => ''.e($cellPad).' font-semibold text-center']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e($label); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $attributes = $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__attributesOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7)): ?>
<?php $component = $__componentOriginal0902e7c2ee22884dce85370b77fe36d7; ?>
<?php unset($__componentOriginal0902e7c2ee22884dce85370b77fe36d7); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($finding['samityRows'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $samityFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="<?php echo e($cellPad); ?> text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(((string) $field === 'date' || str_ends_with((string) $field, '_date') || preg_match('/^date[_\d]/', (string) $field))): ?>
                                        <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'page13Findings.'.e($fIndex).'.samityRows.'.e($rowIndex).'.'.e($field).'','format' => 'dmy','class' => 'w-full border-0 bg-sky-50/50 px-0.5 text-center text-[8px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'page13Findings.'.e($fIndex).'.samityRows.'.e($rowIndex).'.'.e($field).'','format' => 'dmy','class' => 'w-full border-0 bg-sky-50/50 px-0.5 text-center text-[8px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $attributes = $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $component = $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
                                    <?php else: ?>
                                        <input type="text" wire:model.live="page13Findings.<?php echo e($fIndex); ?>.samityRows.<?php echo e($rowIndex); ?>.<?php echo e($field); ?>" class="w-full border-0 bg-sky-50/50 px-0.5 text-center text-[8px]">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <?php echo e($row[$field] ?? ''); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                                <td class="<?php echo e($cellPad); ?> text-center">
                                    <button type="button" wire:click="removePage13SamityRow(<?php echo e($fIndex); ?>, <?php echo e($rowIndex); ?>)" class="text-[10px] text-rose-600">×</button>
                                </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                            <tr>
                                <td colspan="<?php echo e(count($samityFields) + 1); ?>" class="<?php echo e($cellPad); ?> py-3 text-center text-[11px] text-slate-500">
                                    টেবিল খালি — Excel থেকে পেস্ট করুন অথবা + Samity row চাপুন
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <button type="button" wire:click="addPage13SamityRow(<?php echo e($fIndex); ?>)" class="mb-[3mm] text-[11px] font-medium text-[#2b579a]">+ Samity row</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php elseif(($finding['detail_intro'] ?? '') !== '' && ! $editable): ?>
        <p class="mb-[2mm] font-semibold"><?php echo e($finding['detail_intro']); ?></p>
    <?php elseif($editable && $detailType === 'none'): ?>
        <input type="text" wire:model.live="page13Findings.<?php echo e($fIndex); ?>.detail_intro" class="mb-[2mm] w-full rounded border border-slate-200 bg-sky-50/40 px-2 py-1 text-[11px]" placeholder="বিস্তারিত নিম্নে দেওয়া হলো:">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-[2mm] space-y-[2mm] text-[11px] leading-relaxed">
        <div>
            <p class="font-bold">ঝুঁকি:-</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.risk" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
            <?php else: ?>
                <p class="m-0 whitespace-pre-wrap text-justify"><?php echo e(($finding['risk'] ?? '') !== '' ? $finding['risk'] : $dash); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <p class="font-bold">মূল কারণ (Root Cause):</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.root_cause" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
            <?php elseif(($finding['root_cause'] ?? '') !== ''): ?>
                <p class="m-0 text-justify"><?php echo e($finding['root_cause']); ?></p>
            <?php else: ?>
                <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <p class="font-bold">সুপারিশ (Recommendation) :</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.recommendation" rows="2" class="mt-1 w-full rounded border border-slate-200 bg-sky-50/40 p-2 text-[11px]"></textarea>
            <?php elseif(($finding['recommendation'] ?? '') !== ''): ?>
                <p class="m-0 text-justify"><?php echo e($finding['recommendation']); ?></p>
            <?php else: ?>
                <p class="m-0 border-b border-dotted border-black">&nbsp;</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <table class="<?php echo e($compact ? 'a4-table a4-table-compact text-[9px]' : 'w-full border-collapse text-[10.5px]'); ?> <?php echo e($loop->last ? '' : 'mb-[6mm]'); ?>">
        <tbody>
            <tr>
                <td class="<?php echo e($cellPad); ?> w-[38%] font-semibold align-top">শাখা ব্যবস্থাপকের জবাব</td>
                <td class="<?php echo e($cellPad); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.bm_reply" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                    <?php else: ?>
                        <?php echo e($finding['bm_reply'] ?? ''); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="<?php echo e($cellPad); ?> font-semibold align-top">সমস্যা সমাধানের ক্ষেত্রে দায়িত্ব প্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ</td>
                <td class="<?php echo e($cellPad); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <textarea wire:model.live="page13Findings.<?php echo e($fIndex); ?>.responsible" rows="2" class="w-full border-0 bg-sky-50/40 p-1 text-[11px]"></textarea>
                    <?php else: ?>
                        <?php echo e($finding['responsible'] ?? ''); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="<?php echo e($cellPad); ?> font-semibold align-top">সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল <span class="underline decoration-yellow-400 decoration-2">(তারিখ)</span></td>
                <td class="<?php echo e($cellPad); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'page13Findings.'.e($fIndex).'.resolution_date','format' => 'dmy','class' => 'w-full border-0 bg-sky-50/40 px-1 text-[11px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'page13Findings.'.e($fIndex).'.resolution_date','format' => 'dmy','class' => 'w-full border-0 bg-sky-50/40 px-1 text-[11px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $attributes = $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8)): ?>
<?php $component = $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8; ?>
<?php unset($__componentOriginal69d3fb3d18b8321247054b6f17c50ee8); ?>
<?php endif; ?>
                    <?php else: ?>
                        <?php echo e($finding['resolution_date'] ?? ''); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page13-findings-section.blade.php ENDPATH**/ ?>