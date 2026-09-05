
<?php
    $sections = $definition['sections'] ?? [];
?>

<div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <p class="text-[11px] font-semibold text-slate-500">Format: <?php echo e($formatModel?->format_number); ?></p>
        <p class="text-[15px] font-bold text-navy-900"><?php echo e($formatModel?->org_name); ?></p>
        <p class="text-[12px] font-semibold text-slate-700"><?php echo e($formatModel?->dept_name); ?></p>
        <p class="mt-1 text-[13px] font-bold text-navy-900">“<?php echo e($formatModel?->heading); ?>”</p>
    </div>

    <div class="grid gap-3 border-b border-slate-200 px-4 py-3 sm:grid-cols-2">
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">শাখার নাম :</label>
            <input type="text" wire:model.live="shakha_name" class="h-9 w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]">
        </div>
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">নিরীক্ষা কাল :</label>
            <input type="text" wire:model.live="audit_period" class="h-9 w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]">
        </div>
    </div>

    <div class="overflow-x-auto px-2 py-3">
        <table class="min-w-[980px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-10">ক্রঃ নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[140px]">সমিতির নাম ও আইডি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[100px]">সমিতি শুরুর তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[110px]">মাঠকর্মীর নাম</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="5">সমিতি গঠন</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="3">সমিতি বন্ধ /একত্রিকরণ</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="4">সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-16">WP Ref.</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-10"></th>
                </tr>
                <tr class="bg-slate-50 text-center font-semibold text-slate-600">
                    <th class="border border-slate-300" colspan="4"></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="border border-slate-300 px-0.5 py-1 w-8"><?php echo e($i); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 1; $i <= 3; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="border border-slate-300 px-0.5 py-1 w-8"><?php echo e($i); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 1; $i <= 4; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <th class="border border-slate-300 px-0.5 py-1 w-8"><?php echo e($i); ?></th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <th class="border border-slate-300" colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['formation' => 'সমিতি গঠন', 'closure' => 'সমিতি বন্ধ /একত্রিকরণ', 'transfer' => 'সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionKey => $sectionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $rows = $payload['sections'][$sectionKey] ?? []; ?>
                    <tr class="bg-sky-50/70">
                        <td colspan="18" class="border border-slate-300 px-2 py-1.5 text-[12px] font-bold text-navy-900">
                            <div class="flex items-center justify-between gap-2">
                                <span><?php echo e($sectionLabel); ?></span>
                                <button type="button" wire:click="addRow('<?php echo e($sectionKey); ?>')" class="rounded border border-sky-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-[#2b579a] hover:bg-sky-50">+ Row</button>
                            </div>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ri => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'row-'.e($sectionKey).'-'.e($ri).''; ?>wire:key="row-<?php echo e($sectionKey); ?>-<?php echo e($ri); ?>">
                            <td class="border border-slate-300 px-1 py-1 text-center tabular-nums"><?php echo e($ri + 1); ?></td>
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.society_name" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.start_date" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.field_worker" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($c = 0; $c < 5; $c++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="border border-slate-300 p-0.5 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sectionKey === 'formation'): ?>
                                        <select wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.checks.<?php echo e($c); ?>" class="h-8 w-full border-0 bg-transparent p-0 text-center text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                                            <option value=""></option>
                                            <option value="✓">✓</option>
                                            <option value="✗">✗</option>
                                            <option value="N/A">N/A</option>
                                        </select>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($c = 0; $c < 3; $c++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="border border-slate-300 p-0.5 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sectionKey === 'closure'): ?>
                                        <select wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.checks.<?php echo e($c); ?>" class="h-8 w-full border-0 bg-transparent p-0 text-center text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                                            <option value=""></option>
                                            <option value="✓">✓</option>
                                            <option value="✗">✗</option>
                                            <option value="N/A">N/A</option>
                                        </select>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($c = 0; $c < 4; $c++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="border border-slate-300 p-0.5 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sectionKey === 'transfer'): ?>
                                        <select wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.checks.<?php echo e($c); ?>" class="h-8 w-full border-0 bg-transparent p-0 text-center text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                                            <option value=""></option>
                                            <option value="✓">✓</option>
                                            <option value="✗">✗</option>
                                            <option value="N/A">N/A</option>
                                        </select>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.<?php echo e($sectionKey); ?>.<?php echo e($ri); ?>.wp_ref" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                            <td class="border border-slate-300 p-0.5 text-center">
                                <button type="button" wire:click="removeRow(<?php echo e($ri); ?>, '<?php echo e($sectionKey); ?>')" class="text-[11px] text-rose-500 hover:text-rose-700">×</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 border-t border-slate-200 px-4 py-3 lg:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                <p class="mb-2 text-[12px] font-bold text-navy-900"><?php echo e($section['label']); ?></p>
                <ol class="list-decimal space-y-1.5 pl-4 text-[11px] leading-snug text-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $section['questions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li><?php echo e($q); ?></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ol>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div class="border-t border-slate-200 px-4 py-3">
        <label class="mb-1 block text-[12px] font-bold text-navy-900">সারসংক্ষেপ:</label>
        <textarea
            wire:model.live="summary"
            rows="4"
            class="w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
            placeholder="সারসংক্ষেপ লিখুন…"
        ></textarea>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-checklist-format-1-editor.blade.php ENDPATH**/ ?>