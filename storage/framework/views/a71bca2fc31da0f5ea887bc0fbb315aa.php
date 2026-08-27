<?php
    $parts = \App\Livewire\MakeAuditReport::findingRatingParts($rating ?? '');
    $label = $parts['label'] ?: '—';
    $code = $parts['code'] ?: '—';
    $editable = $editable ?? false;
    $wireModel = $wireModel ?? null;
?>
<table class="w-full border-collapse text-[10px]">
    <tr>
        <td colspan="2" class="border border-black bg-[#4472C4] p-[1.5mm] text-center text-[9px] font-bold text-white">
            রেটিং (Rating)
        </td>
    </tr>
    <tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable && $wireModel): ?>
            <td colspan="2" class="border border-black bg-[#F8CBAD] p-[1.5mm] text-center font-bold">
                <select wire:model.live="<?php echo e($wireModel); ?>" class="w-full border-0 bg-transparent text-center text-[10px] font-bold">
                    <option value="">—</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $findingRatings ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($option !== ''): ?>
                            <option value="<?php echo e($option); ?>"><?php echo e($option); ?></option>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </td>
        <?php else: ?>
            <td class="w-1/2 border border-black bg-[#F8CBAD] p-[1.5mm] text-center font-bold"><?php echo e($label); ?></td>
            <td class="w-1/2 border border-black bg-[#F8CBAD] p-[1.5mm] text-center text-[11px] font-bold"><?php echo e($code); ?></td>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-rating-box.blade.php ENDPATH**/ ?>