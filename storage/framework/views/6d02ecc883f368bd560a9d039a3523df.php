<div class="audit-wizard" style="font-family:'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;">
    <link href="https://fonts.bunny.net/css?family=hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 'select'): ?>
        <div
            class="px-4 py-5 lg:px-6"
            x-data="{
                q: '',
                open: false,
                highlight: 0,
                selectedId: <?php echo \Illuminate\Support\Js::from($shakha_id ? (string) $shakha_id : '')->toHtml() ?>,
                selectedLabel: <?php echo \Illuminate\Support\Js::from($selectedShakhaLabel ?: '')->toHtml() ?>,
                branches: <?php echo \Illuminate\Support\Js::from($branchOptions)->toHtml() ?>,
                get filtered() {
                    const q = this.q.trim().toLowerCase();
                    if (!q) return this.branches;
                    return this.branches.filter((b) => {
                        const hay = (b.name + ' ' + b.code + ' ' + b.area + ' ' + b.division + ' ' + b.focal).toLowerCase();
                        return hay.includes(q);
                    });
                },
                pick(b) {
                    this.selectedId = String(b.id);
                    this.selectedLabel = b.name + (b.code ? ' (' + b.code + ')' : '') + (b.area ? ' — ' + b.area : '');
                    this.q = '';
                    this.open = false;
                    this.highlight = 0;
                    $wire.set('shakha_id', Number(b.id));
                },
                clear() {
                    this.q = '';
                    this.selectedId = '';
                    this.selectedLabel = '';
                    this.open = false;
                    this.highlight = 0;
                    $wire.set('shakha_id', null);
                },
                onKey(e) {
                    const list = this.filtered;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.open = true;
                        this.highlight = Math.min(this.highlight + 1, Math.max(list.length - 1, 0));
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlight = Math.max(this.highlight - 1, 0);
                    } else if (e.key === 'Enter' && list[this.highlight]) {
                        e.preventDefault();
                        this.pick(list[this.highlight]);
                    } else if (e.key === 'Escape') {
                        this.open = false;
                        this.q = '';
                    }
                }
            }"
        >
            <div class="px-4 py-4 lg:px-6">
                <div class="mb-3">
                    <h1 class="text-[16px] font-semibold text-navy-900">Audit Report Dashboard</h1>
                    <p class="mt-0.5 text-[11px] text-slate-500">একসাথে সর্বোচ্চ <?php echo e($maxConcurrentDrafts); ?>টি রিপোর্ট · Auto-save · Continue দিয়ে আগের কাজ চালিয়ে যান</p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                    <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] text-emerald-800"><?php echo e(session('status')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php echo $__env->make('livewire.partials.audit-reports-dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    <?php else: ?>
        <div
            class="border-b border-slate-200 bg-white px-3 py-1.5 lg:px-4"
            wire:poll.30s="autoSaveDraft"
            x-data="{ dlOpen: false }"
        >
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="backToSelect"
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                    title="Back to dashboard"
                >
                    ←
                </button>
                <div class="min-w-0 flex-1">
                    <div class="flex min-w-0 flex-wrap items-baseline gap-x-2 gap-y-0">
                        <h1 class="truncate text-[13px] font-semibold leading-tight text-navy-900">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h1>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSaveHint !== ''): ?>
                            <span class="shrink-0 text-[10px] font-medium text-emerald-700" wire:loading.remove wire:target="autoSaveDraft"><?php echo e($autoSaveHint); ?></span>
                            <span class="shrink-0 text-[10px] font-medium text-slate-400" wire:loading wire:target="autoSaveDraft">Saving…</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="truncate text-[10px] leading-tight text-slate-500"><?php echo e($shakha_display_name); ?> · <?php echo e($area_display_name); ?> · <?php echo e($monthLabel); ?> <?php echo e($report_year); ?></p>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    <button
                        type="button"
                        wire:click="autoSaveDraft"
                        class="inline-flex h-7 items-center justify-center rounded-md border border-slate-200 px-2 text-[13px] hover:bg-slate-50"
                        title="Save now"
                    >💾</button>
                    <button
                        type="button"
                        wire:click="openPreview"
                        class="inline-flex h-7 items-center justify-center rounded-md border border-slate-200 px-2 text-[13px] hover:bg-sky-50"
                        title="Preview"
                    >👁️</button>

                    <div class="relative">
                        <button
                            type="button"
                            @click="dlOpen = !dlOpen"
                            class="inline-flex h-7 items-center gap-1 rounded-md border border-slate-200 bg-white px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                            title="Download as"
                        >
                            ⬇️ <span class="hidden sm:inline">Download as</span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div
                            x-show="dlOpen"
                            x-cloak
                            @click.outside="dlOpen = false"
                            class="absolute right-0 z-30 mt-1 w-36 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                        >
                            <button
                                type="button"
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                @click="dlOpen = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-[12px] text-slate-700 hover:bg-emerald-50 disabled:opacity-60"
                            >
                                <span class="font-semibold text-emerald-700">PDF</span>
                                <span wire:loading wire:target="downloadPdf" class="text-[10px] text-slate-400">…</span>
                            </button>
                            <button
                                type="button"
                                wire:click="downloadDoc"
                                wire:loading.attr="disabled"
                                wire:target="downloadDoc"
                                @click="dlOpen = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-[12px] text-slate-700 hover:bg-sky-50 disabled:opacity-60"
                            >
                                <span class="font-semibold text-[#2b579a]">Doc</span>
                                <span wire:loading wire:target="downloadDoc" class="text-[10px] text-slate-400">…</span>
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="saveCover"
                        class="inline-flex h-7 items-center gap-1 rounded-md bg-[#2b579a] px-2.5 text-[11px] font-medium text-white hover:bg-[#204072]"
                        title="সংরক্ষণ"
                    >✅ সংরক্ষণ</button>
                </div>
            </div>

            
        </div>

        <div
            class="flex min-h-0 flex-1"
            x-data="{
                open: true,
                init() {
                    const saved = localStorage.getItem('auditOutlineOpen');
                    if (saved === '0') this.open = false;
                    if (saved === '1') this.open = true;
                    this.$watch('open', (v) => localStorage.setItem('auditOutlineOpen', v ? '1' : '0'));
                }
            }"
        >
            
            <aside
                class="sticky top-0 z-10 hidden h-[calc(100vh-0.5rem)] max-h-screen shrink-0 self-start border-r border-slate-200 bg-white transition-[width] duration-200 ease-out lg:flex lg:flex-col"
                :class="open ? 'w-[200px]' : 'w-9'"
                :title="open ? '' : 'শিরোনাম খুলুন'"
            >
                <div
                    class="flex shrink-0 items-center gap-1 border-b border-slate-100"
                    :class="open ? 'justify-between px-2 py-2' : 'flex-col justify-start gap-2 px-0 py-2'"
                >
                    <template x-if="open">
                        <div class="min-w-0 flex-1 px-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">শিরোনাম</p>
                            <p class="truncate text-[9px] text-slate-500">ক্লিক = স্ক্রল</p>
                        </div>
                    </template>

                    <button
                        type="button"
                        @click="open = ! open"
                        class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                        :aria-expanded="open"
                        :aria-label="open ? 'সাইডবার বন্ধ' : 'সাইডবার খুলুন'"
                    >
                        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>

                
                <button
                    type="button"
                    x-show="! open"
                    x-cloak
                    @click="open = true"
                    class="flex flex-1 flex-col items-center gap-2 px-0.5 py-3 text-[10px] font-semibold tracking-wide text-slate-500 hover:bg-slate-50 hover:text-[#2b579a]"
                >
                    <span class="select-none" style="writing-mode: vertical-rl; text-orientation: mixed;">শিরোনাম</span>
                </button>

                <nav
                    class="min-h-0 flex-1 space-y-0.5 overflow-y-auto px-1.5 py-1.5"
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $outlineNav ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $isActiveTab = ($activeTab ?? '') === ($item['tab'] ?? '');
                            $depth = (int) ($item['depth'] ?? 0);
                            $kind = $item['kind'] ?? '';
                            $activeFixed = $isActiveTab && $kind === 'fixed';
                        ?>
                        <button
                            type="button"
                            wire:click="goToOutlineItem(<?php echo \Illuminate\Support\Js::from($item['tab'])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($item['anchor'])->toHtml() ?>)"
                            class="block w-full rounded-md px-2 py-1 text-left text-[11px] leading-snug transition
                                <?php echo e($depth > 0 ? 'pl-3.5' : ''); ?>

                                <?php echo e($kind === 'section' ? 'font-semibold' : ''); ?>

                                <?php echo e($activeFixed ? 'bg-[#2b579a] text-white' : 'text-slate-700 hover:bg-slate-100'); ?>"
                            title="<?php echo e($item['label']); ?>"
                        >
                            <span class="line-clamp-2"><?php echo e($item['label']); ?></span>
                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </nav>
            </aside>

            
            <div class="min-w-0 flex-1">
                
                <div class="border-b border-slate-200 bg-white px-3 py-2 lg:hidden" x-data>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">শিরোনাম</label>
                    <select
                        class="w-full rounded-md border border-slate-200 bg-white px-2 py-1.5 text-[12px] text-slate-800"
                        @change="
                            const v = $event.target.value;
                            if (!v) return;
                            const i = v.indexOf('|');
                            const tab = i >= 0 ? v.slice(0, i) : v;
                            const anchor = i >= 0 ? v.slice(i + 1) : '';
                            $wire.goToOutlineItem(tab, anchor);
                        "
                    >
                        <option value="">যে শিরোনামে যেতে চান…</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $outlineNav ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($item['tab']); ?>|<?php echo e($item['anchor']); ?>"><?php echo e($item['label']); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-emerald-50 px-4 py-2 text-[12px] text-emerald-800 lg:px-6"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'cover'): ?>
        <div id="audit-cover" class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-[12px] font-semibold text-slate-800">১. Cover Page — ইনপুট ফর্ম</p>
                <span class="text-[11px] text-slate-500">নীল ঘরগুলো পূরণ করুন · Preview দিয়ে ডাউনলোড দেখুন</span>
            </div>

            <div class="cover-form mx-auto rounded-sm bg-white shadow-lg">
                <div class="cover-inner text-[12.5px] leading-relaxed text-slate-900">
                    <?php echo $__env->make('livewire.partials.audit-cover-letterhead', [
                        'editable' => true,
                        'logoUrl' => $logoUrl,
                        'ratingColor' => $ratingColor,
                        'control_rating' => $control_rating,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="mt-4 space-y-2">
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">সূত্র নাম্বার:</span>
                            <input type="text" wire:model.live="memo_no" class="inline-input min-w-[220px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">তারিখ:</span>
                            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'report_date','format' => 'iso','class' => 'inline-input']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'report_date','format' => 'iso','class' => 'inline-input']); ?>
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
                        </p>
                    </div>

                    <div class="mt-5 leading-relaxed">
                        <p>বরাবর,</p>
                        <p>যুগ্ম পরিচালক (নিরীক্ষা)</p>
                        <p>দুঃস্থ স্বাস্থ্য কেন্দ্র (ডিএসকে)</p>
                        <p>প্রধান কার্যালয়, ঢাকা।</p>
                    </div>

                    <h2 class="mt-5 text-center text-[15px] font-bold underline decoration-1 underline-offset-4">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h2>

                    <div class="mt-4 space-y-2">
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">শাখার নাম ও নাম্বার:</span>
                            <input type="text" wire:model.live="shakha_display_name" class="inline-input min-w-[200px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">অঞ্চলের নাম:</span>
                            <input type="text" wire:model.live="area_display_name" class="inline-input min-w-[200px] flex-1">
                        </p>
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">নিরীক্ষাকাল:</span>
                            <input type="text" wire:model.live="audit_period_label" class="inline-input min-w-[200px] flex-1">
                        </p>
                    </div>

                    <div class="mt-5 leading-[1.85]">
                        <p class="font-semibold">প্রিয় মহোদয়,</p>
                        <p class="mt-2 text-justify">
                            গত
                            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'audit_start_date','format' => 'iso','class' => 'inline-input mx-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'audit_start_date','format' => 'iso','class' => 'inline-input mx-1']); ?>
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
                            হতে
                            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'audit_end_date','format' => 'iso','class' => 'inline-input mx-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'audit_end_date','format' => 'iso','class' => 'inline-input mx-1']); ?>
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
                            পর্যন্ত মোট
                            <input type="number" min="0" wire:model.live="working_days" class="inline-input mx-1 w-16 text-center">
                            কর্ম দিবস
                            <input type="text" wire:model.live="shakha_display_name" class="inline-input mx-1 min-w-[140px]">
                            শাখা হতে
                            <input type="text" wire:model.live="period_scope" class="inline-input mx-1 min-w-[140px]">
                            সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
                            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'draft_sent_date','format' => 'iso','class' => 'inline-input mx-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'draft_sent_date','format' => 'iso','class' => 'inline-input mx-1']); ?>
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
                            ইং তারিখে প্রেরণ করা হয় এবং
                            <?php if (isset($component)) { $__componentOriginal69d3fb3d18b8321247054b6f17c50ee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69d3fb3d18b8321247054b6f17c50ee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.audit-date-field','data' => ['wire:model.live' => 'comments_received_date','format' => 'iso','class' => 'inline-input mx-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('audit-date-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'comments_received_date','format' => 'iso','class' => 'inline-input mx-1']); ?>
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
                            তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।
                        </p>
                    </div>

                    <div class="mt-6">
                        <p>আপনার বিশ্বস্ত,</p>
                        <p class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">নাম:</span>
                            <input type="text" wire:model.live="auditor_name" class="inline-input min-w-[180px] flex-1">
                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['auditor_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <p class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="font-semibold shrink-0">পদবী:</span>
                            <input type="text" wire:model.live="auditor_designation" class="inline-input min-w-[180px] flex-1">
                        </p>
                    </div>

                    <div class="mt-6 text-[11.5px] leading-relaxed">
                        <p class="font-semibold">অনুলিপি:</p>
                        <ol class="ml-4 list-decimal space-y-0.5">
                            <li>নির্বাহী পরিচালক</li>
                            <li>উপ-নির্বাহী পরিচালক</li>
                            <li>পরিচালক ঋণ</li>
                            <li>উপ-প্রধান ঋণ</li>
                            <li>যুগ্ম পরিচালক প্রশাসন ও মানব সম্পদ</li>
                            <li>ফোকাল পার্সন</li>
                            <li>অঞ্চলিক ব্যবস্থাপক</li>
                            <li>শাখা ব্যবস্থাপক</li>
                            <li>অফিস কপি</li>
                        </ol>
                    </div>

                    <div class="mt-8 flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
                        <p class="text-[11px] text-slate-500">পৃষ্ঠা ১ / Cover Page</p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                            <button type="button" wire:click="saveCover" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ ও পরবর্তী →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif($activeTab === 'page2'): ?>
            <div id="audit-page2">
                <?php echo $__env->make('livewire.partials.audit-page2-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php elseif($activeTab === 'page3'): ?>
            <div id="audit-page3">
                <?php echo $__env->make('livewire.partials.audit-page3-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php elseif($activeTab === 'page4'): ?>
            <div id="audit-page4">
                <?php echo $__env->make('livewire.partials.audit-page4-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php else: ?>
            <div class="px-4 py-16 text-center text-[13px] text-slate-500">
                এই পৃষ্ঠা এখনো যোগ করা হয়নি। Cover Page শেষ করে পরের ছবি পাঠালে ট্যাব যোগ করা হবে।
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPreview): ?>
            <?php echo $__env->make('livewire.partials.audit-document-preview-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="fixed inset-0 z-50 flex flex-col bg-slate-900/60" wire:click.self="closePreview">
                <div class="mx-auto w-full max-w-[236mm] shrink-0 px-3 pt-4">
                    <div class="flex items-center justify-between rounded-lg bg-white px-4 py-2.5 shadow-lg ring-1 ring-black/5">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Preview</p>
                            <p class="text-[11px] text-slate-500">A4 · Cover আলাদা · বাকি অংশ একসাথে বসে (ফাঁকা পৃষ্ঠা নয়)</p>
                        </div>
                        <div class="flex items-center gap-1.5" x-data="{ dlOpen: false }">
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="dlOpen = !dlOpen"
                                    class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    ⬇️ Download as
                                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div
                                    x-show="dlOpen"
                                    x-cloak
                                    @click.outside="dlOpen = false"
                                    class="absolute right-0 z-30 mt-1 w-36 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                                >
                                    <button
                                        type="button"
                                        wire:click="downloadPdf"
                                        wire:loading.attr="disabled"
                                        wire:target="downloadPdf"
                                        @click="dlOpen = false"
                                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-[12px] text-slate-700 hover:bg-emerald-50 disabled:opacity-60"
                                    >
                                        <span class="font-semibold text-emerald-700">PDF</span>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="downloadDoc"
                                        wire:loading.attr="disabled"
                                        wire:target="downloadDoc"
                                        @click="dlOpen = false"
                                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-[12px] text-slate-700 hover:bg-sky-50 disabled:opacity-60"
                                    >
                                        <span class="font-semibold text-[#2b579a]">Doc</span>
                                    </button>
                                </div>
                            </div>
                            <button type="button" wire:click="closePreview" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">বন্ধ</button>
                        </div>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-3 py-4" wire:click.self="closePreview">
                    <div class="mx-auto w-full max-w-[236mm]">
                    <div class="audit-doc-preview rounded-sm bg-[#8d8d8d] px-4 py-6">
                        <?php echo $__env->make('livewire.partials.audit-document-preview-pages', [
                            'documentSheets' => $documentSheets,
                            'logoUrl' => $logoUrl,
                            'ratingColor' => $ratingColor,
                            'control_rating' => $control_rating,
                            'memo_no' => $memo_no,
                            'report_date' => $report_date,
                            'shakha_display_name' => $shakha_display_name,
                            'area_display_name' => $area_display_name,
                            'audit_period_label' => $audit_period_label,
                            'audit_start_date' => $audit_start_date,
                            'audit_end_date' => $audit_end_date,
                            'working_days' => $working_days,
                            'period_scope' => $period_scope,
                            'draft_sent_date' => $draft_sent_date,
                            'comments_received_date' => $comments_received_date,
                            'auditor_name' => $auditor_name,
                            'auditor_designation' => $auditor_designation,
                            'glance_as_of' => $glance_as_of,
                            'branch_opening_date' => $branch_opening_date,
                            'staff_info_as_of' => $staff_info_as_of,
                            'glanceRows' => $glanceRows,
                            'staffColumns' => $staffColumns,
                            'staffRows' => $staffRows,
                            'sign_auditor_name' => $sign_auditor_name,
                            'sign_auditor_designation' => $sign_auditor_designation,
                            'sign_auditor_date' => $sign_auditor_date,
                            'sign_bm_name' => $sign_bm_name,
                            'sign_bm_date' => $sign_bm_date,
                            'sign_abm_name' => $sign_abm_name,
                            'sign_abm_date' => $sign_abm_date,
                            'financial_section_title' => $financial_section_title,
                            'financialFindings' => $financialFindings,
                            'reportSections' => $reportSections ?? [],
                            'reportBlocks' => $reportBlocks ?? [],
                            'financial_criteria' => $financial_criteria,
                            'vatObservationRows' => $vatObservationRows,
                            'taxObservationRows' => $taxObservationRows,
                            'tableHeaders' => $tableHeaders ?? [],
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<style>
    .field-label {
        display: block;
        margin-bottom: 4px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .field-input {
        width: 100%;
        height: 36px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 0 10px;
        font-size: 13px;
        font-family: inherit;
        color: #334155;
        line-height: 36px;
    }
    .inline-input {
        display: inline-block;
        height: 28px;
        border: 1px solid #93c5fd;
        border-radius: 4px;
        background: #eff6ff;
        padding: 0 8px;
        font-size: 12px;
        color: #0f172a;
        line-height: 26px;
        vertical-align: middle;
    }
    .inline-input:focus {
        outline: none;
        border-color: #2b579a;
        box-shadow: 0 0 0 1px #2b579a;
        background: #fff;
    }
    .audit-date-field {
        vertical-align: middle;
    }
    .audit-date-field > input[type="text"] {
        min-width: 0;
        width: 100%;
    }
    table .audit-date-field {
        min-width: 6.5rem;
    }
    table .audit-date-picker-hit {
        width: 1.5rem;
        min-width: 1.5rem;
    }
    /* Bangla UI + digits: Hind Siliguri (clear ১). Do not prefer Noto — its ১ looks poor on web. */
    .audit-tab-pill,
    .audit-tab-label {
        font-family: 'Hind Siliguri', 'Nirmala UI', Inter, system-ui, sans-serif;
    }
    .audit-tab-label .bn-num,
    .bn-num.bn-tab {
        font-family: 'Hind Siliguri', 'Nirmala UI', sans-serif;
        font-weight: 700;
        letter-spacing: 0.02em;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    .audit-tab-index {
        font-family: Inter, system-ui, sans-serif;
        font-variant-numeric: tabular-nums;
    }
    .audit-wizard input,
    .audit-wizard textarea,
    .audit-wizard select,
    .audit-wizard button {
        font-family: inherit;
    }
    .finding-serial-cell,
    .finding-serial-input,
    .finding-heading,
    .finding-heading .bn-num,
    .bn-num.bn-serial {
        font-family: 'Hind Siliguri', 'Nirmala UI', Arial, sans-serif !important;
        font-weight: 700;
        letter-spacing: 0.02em;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    .finding-serial-input {
        font-size: inherit;
        line-height: 1.35;
        color: inherit;
    }
    /* Editor sheet — same A4 margins as submitted document */
    .cover-form {
        width: 210mm;
        max-width: 100%;
    }
    .cover-inner {
        padding: 15mm 20mm 15mm;
        color: #111;
        font-size: 12pt;
        line-height: 1.45;
        min-height: 297mm;
        box-sizing: border-box;
    }
    .dotted {
        border-bottom: 1px dotted #111;
        padding: 0 2px 1px;
        font-weight: 600;
    }
    /* Form editor tables (pages 2–4 input views) */
    .a4-table {
        width: 100%;
        border-collapse: collapse;
    }
    .a4-table th,
    .a4-table td {
        border: 1px solid #111;
        padding: 1.6mm 1.8mm;
        vertical-align: middle;
    }
    .a4-table th {
        font-weight: 600;
        background: #d9d9d9;
    }
    .a4-table-compact th,
    .a4-table-compact td {
        padding: 1.2mm 1.4mm;
    }
</style>
</div>

<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/make-audit-report.blade.php ENDPATH**/ ?>