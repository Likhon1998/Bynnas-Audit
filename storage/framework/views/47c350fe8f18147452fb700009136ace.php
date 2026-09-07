<?php
    $editable = $editable ?? false;
    $cellPad = $cellPad ?? 'border border-slate-800 px-1.5 py-1';
    $variant = $variant ?? 'stats'; // stats | stats_alt
    $headers = $tableHeaders[$variant] ?? \App\Support\AuditTableHeaders::defaults()[$variant];
    $inputClass = 'w-full border-0 bg-transparent px-0.5 text-center text-[10px] font-semibold text-white placeholder-white/60 focus:bg-white/15 focus:ring-1 focus:ring-white/40';
?>
<thead>
    <tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hi => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal0902e7c2ee22884dce85370b77fe36d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0902e7c2ee22884dce85370b77fe36d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-th','data' => ['editable' => $editable,'wire' => 'tableHeaders.'.$variant.'.'.$hi,'class' => ''.e($cellPad).' bg-[#5b2a86] font-semibold text-white','inputClass' => $inputClass]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['editable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editable),'wire' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('tableHeaders.'.$variant.'.'.$hi),'class' => ''.e($cellPad).' bg-[#5b2a86] font-semibold text-white','input-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inputClass)]); ?>
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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <th class="<?php echo e($cellPad); ?> bg-[#5b2a86] text-white"></th>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tr>
</thead>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-stats-thead.blade.php ENDPATH**/ ?>