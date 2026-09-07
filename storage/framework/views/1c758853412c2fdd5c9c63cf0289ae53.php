
<?php
    $questions = $definition['questions'] ?? [];
    $checkCount = (int) ($definition['check_count'] ?? 11);
    $rowCount = (int) ($definition['default_rows'] ?? 8);
?>

<div class="pointer-events-none select-none overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm opacity-95">
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <p class="text-[11px] font-semibold text-slate-500">Format: <?php echo e($formatModel?->format_number); ?></p>
        <p class="text-[15px] font-bold text-navy-900"><?php echo e($formatModel?->org_name); ?></p>
        <p class="text-[12px] font-semibold text-slate-700"><?php echo e($formatModel?->dept_name); ?></p>
        <p class="mt-1 text-[13px] font-bold text-navy-900">“<?php echo e($formatModel?->heading); ?>”</p>
    </div>

    <div class="grid gap-3 border-b border-slate-200 px-4 py-3 sm:grid-cols-2">
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">শাখার নাম :</label>
            <div class="flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] text-slate-400">…………………………</div>
        </div>
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">নিরীক্ষা কাল :</label>
            <div class="flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[12px] text-slate-400">…………………………</div>
        </div>
    </div>

    <div class="overflow-x-auto px-2 py-3">
        <table class="min-w-[1100px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-10">ক্রঃ নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[130px]">সমিতির নাম ও আইডি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[110px]">সংশ্লিষ্ট এফ ও এর নাম</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[140px]">সমিতির সদস্যদের নাম ও আইডি</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 1; $i <= $checkCount; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="border border-slate-300 px-0.5 py-1.5 w-8"><?php echo e($i); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <th class="border border-slate-300 px-1 py-1.5 w-16">WP Ref</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($ri = 0; $ri < $rowCount; $ri++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="border border-slate-300 px-1 py-2 text-center tabular-nums"><?php echo e($ri + 1); ?></td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($c = 0; $c < $checkCount; $c++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <td class="border border-slate-300 px-1 py-2 text-center text-slate-300">□</td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <td class="border border-slate-300 px-1 py-2">&nbsp;</td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <p class="mb-2 text-[12px] font-bold text-navy-900">চেকলিস্ট পয়েন্ট (১–<?php echo e($checkCount); ?>)</p>
        <ol class="columns-1 gap-x-6 space-y-1 text-[11px] leading-snug text-slate-700 md:columns-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qi => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li class="break-inside-avoid pl-1"><span class="font-semibold text-slate-800"><?php echo e($qi + 1); ?>.</span> <?php echo e($q); ?></li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ol>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <p class="mb-1 text-[12px] font-bold text-navy-900">সারসংক্ষেপ:</p>
        <div class="min-h-[96px] rounded-md border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-300">…………………………………………</div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-checklist-format-2-preview.blade.php ENDPATH**/ ?>