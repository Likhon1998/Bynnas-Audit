<?php
    /** @var int $previewPage */
    $previewPage = $previewPage ?? 2;
    $hToc = $tableHeaders['toc'] ?? \App\Support\AuditTableHeaders::defaults()['toc'];
    $tocWidths = ['w-[70px]', '', 'w-[90px]', 'w-[130px]', 'w-[100px]', 'w-[70px]'];
?>

<div class="mb-2 flex flex-wrap items-center gap-2">
    <p class="mr-auto text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        সূচিপত্র <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previewPage === 2): ?> (শুরু) <?php else: ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </p>
    <button type="button" wire:click="addTocSection(-1, <?php echo e($previewPage); ?>)" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Section</button>
    <button type="button" wire:click="addTocRow(-1, <?php echo e($previewPage); ?>)" class="h-7 rounded border border-slate-300 px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50">+ Row</button>
</div>

<div class="overflow-x-auto">
    <table class="w-full min-w-[900px] border-collapse text-[11px]">
        <thead>
            <tr class="bg-slate-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hToc; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hi => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => true,'wire' => 'tableHeaders.toc.'.$hi,'class' => ''.e($tocWidths[$hi] ?? '').' border border-slate-800 px-1 py-1.5 font-semibold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => true,'wire' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('tableHeaders.toc.'.$hi),'class' => ''.e($tocWidths[$hi] ?? '').' border border-slate-800 px-1 py-1.5 font-semibold']); ?>
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
                <th class="w-[70px] border border-slate-800 px-1 py-1.5 font-semibold">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tocRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if((int) ($row['preview_page'] ?? 2) !== $previewPage): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php continue; ?><?php endif; ?>
                <?php
                    $isSection = ($row['type'] ?? 'item') === 'section';
                    $ratingStyle = \App\Livewire\MakeAuditReport::findingRatingStyle($row['rating'] ?? '');
                ?>
                <tr class="<?php echo e($isSection ? 'bg-slate-100' : ''); ?>">
                    <td class="border border-slate-800 px-1 py-1">
                        <input type="text" wire:model.live="tocRows.<?php echo e($idx); ?>.serial" class="finding-serial-input h-7 w-full border-0 bg-sky-50 px-1 text-center text-[11px] font-semibold focus:ring-1 focus:ring-sky-400">
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        <input type="text" wire:model.live="tocRows.<?php echo e($idx); ?>.finding" class="h-7 w-full border-0 bg-sky-50 px-1 text-[11px] <?php echo e($isSection ? 'font-bold' : ''); ?> focus:ring-1 focus:ring-sky-400" placeholder="<?php echo e($isSection ? 'Section title' : 'Finding'); ?>">
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSection): ?>
                            <span class="block text-center text-slate-400">—</span>
                        <?php else: ?>
                            <input type="text" wire:model.live="tocRows.<?php echo e($idx); ?>.amount" class="h-7 w-full border-0 bg-sky-50 px-1 text-right text-[11px] focus:ring-1 focus:ring-sky-400">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="border border-slate-800 px-0 py-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSection): ?>
                            <span class="block px-1 text-center text-slate-400">—</span>
                        <?php else: ?>
                            <select
                                wire:model.live="tocRows.<?php echo e($idx); ?>.rating"
                                class="h-8 w-full border-0 px-1 text-[11px] font-semibold focus:ring-1 focus:ring-sky-400"
                                style="background: <?php echo e($ratingStyle['bg']); ?>; color: <?php echo e($ratingStyle['color']); ?>;"
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $findingRatings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($opt); ?>"><?php echo e($opt !== '' ? $opt : '—'); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSection): ?>
                            <span class="block text-center text-slate-400">—</span>
                        <?php else: ?>
                            <input type="text" wire:model.live="tocRows.<?php echo e($idx); ?>.status" class="h-7 w-full border-0 bg-sky-50 px-1 text-[11px] focus:ring-1 focus:ring-sky-400">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="border border-slate-800 px-1 py-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSection): ?>
                            <span class="block text-center text-slate-400">—</span>
                        <?php else: ?>
                            <input type="text" wire:model.live="tocRows.<?php echo e($idx); ?>.page_no" class="h-7 w-full border-0 bg-sky-50 px-1 text-center text-[11px] focus:ring-1 focus:ring-sky-400">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="border border-slate-800 px-1 py-1 text-center">
                        <button type="button" wire:click="removeTocRow(<?php echo e($idx); ?>)" class="text-[11px] text-rose-600 hover:underline">×</button>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-toc-table-form.blade.php ENDPATH**/ ?>