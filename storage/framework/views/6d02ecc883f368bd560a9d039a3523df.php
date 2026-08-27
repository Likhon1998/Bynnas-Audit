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
            class="border-b border-slate-200 bg-white px-4 py-3 lg:px-6"
            wire:poll.30s="autoSaveDraft"
        >
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    wire:click="backToSelect"
                    class="inline-flex h-8 shrink-0 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-[12px] font-medium text-slate-700 hover:bg-slate-50"
                    title="Audit Dashboard"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-[15px] font-semibold text-navy-900">অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h1>
                    <p class="truncate text-[11px] text-slate-500"><?php echo e($shakha_display_name); ?> · <?php echo e($area_display_name); ?> · <?php echo e($monthLabel); ?> <?php echo e($report_year); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSaveHint !== ''): ?>
                        <p class="mt-0.5 text-[10px] font-medium text-emerald-700" wire:loading.remove wire:target="autoSaveDraft"><?php echo e($autoSaveHint); ?></p>
                        <p class="mt-0.5 text-[10px] font-medium text-slate-400" wire:loading wire:target="autoSaveDraft">Saving…</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button type="button" wire:click="autoSaveDraft" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">Save now</button>
                <button type="button" wire:click="openPreview" class="h-8 rounded-lg border border-[#2b579a] bg-white px-3 text-[12px] font-semibold text-[#2b579a] hover:bg-sky-50">Preview</button>
                <button
                    type="button"
                    wire:click="downloadPdf"
                    wire:loading.attr="disabled"
                    wire:target="downloadPdf"
                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-emerald-600 bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    <span wire:loading.remove wire:target="downloadPdf">PDF Download</span>
                    <span wire:loading wire:target="downloadPdf">Downloading…</span>
                </button>
                <button type="button" wire:click="saveCover" class="h-8 rounded-lg bg-[#2b579a] px-3 text-[12px] font-medium text-white hover:bg-[#204072]">সংরক্ষণ</button>
            </div>

            
            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                <span class="mr-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">পৃষ্ঠা ক্রম</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button
                        type="button"
                        <?php if($tab['ready']): ?> wire:click="$set('activeTab', '<?php echo e($tab['id']); ?>')" <?php endif; ?>
                        <?php if(! $tab['ready']): echo 'disabled'; endif; ?>
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-medium transition
                            <?php echo e($activeTab === $tab['id'] ? 'bg-[#2b579a] text-white' : ($tab['ready'] ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'cursor-not-allowed bg-slate-50 text-slate-400')); ?>"
                    >
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold
                            <?php echo e($activeTab === $tab['id'] ? 'bg-white/20 text-white' : 'bg-white text-slate-500'); ?>"><?php echo e($tab['num']); ?></span>
                        <?php echo e($tab['label']); ?>

                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?>
                        <span class="text-slate-300">→</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="bg-emerald-50 px-4 py-2 text-[12px] text-emerald-800 lg:px-6"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'cover'): ?>
        <div class="border-b border-slate-200 bg-slate-100 px-3 py-5 lg:px-6">
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
                            <input type="date" wire:model.live="report_date" class="inline-input">
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
                            <input type="date" wire:model.live="audit_start_date" class="inline-input mx-1">
                            হতে
                            <input type="date" wire:model.live="audit_end_date" class="inline-input mx-1">
                            পর্যন্ত মোট
                            <input type="number" min="0" wire:model.live="working_days" class="inline-input mx-1 w-16 text-center">
                            কর্ম দিবস
                            <input type="text" wire:model.live="shakha_display_name" class="inline-input mx-1 min-w-[140px]">
                            শাখা হতে
                            <input type="text" wire:model.live="period_scope" class="inline-input mx-1 min-w-[140px]">
                            সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
                            <input type="date" wire:model.live="draft_sent_date" class="inline-input mx-1">
                            ইং তারিখে প্রেরণ করা হয় এবং
                            <input type="date" wire:model.live="comments_received_date" class="inline-input mx-1">
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
            <?php echo $__env->make('livewire.partials.audit-page2-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($activeTab === 'page3'): ?>
            <?php echo $__env->make('livewire.partials.audit-page3-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($activeTab === 'page4'): ?>
            <?php echo $__env->make('livewire.partials.audit-page4-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <div class="px-4 py-16 text-center text-[13px] text-slate-500">
                এই পৃষ্ঠা এখনো যোগ করা হয়নি। Cover Page শেষ করে পরের ছবি পাঠালে ট্যাব যোগ করা হবে।
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPreview): ?>
            <?php echo $__env->make('livewire.partials.audit-document-preview-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 px-3 py-6" wire:click.self="closePreview">
                <div class="mx-auto w-full max-w-[236mm]">
                    <div class="mb-3 flex items-center justify-between rounded-lg bg-white px-4 py-2.5 shadow">
                        <div>
                            <p class="text-[13px] font-semibold text-navy-900">Preview</p>
                            <p class="text-[11px] text-slate-500">A4 · Cover আলাদা · বাকি অংশ একসাথে বসে (ফাঁকা পৃষ্ঠা নয়)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-[12px] font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                                <span wire:loading.remove wire:target="downloadPdf">PDF Download</span>
                                <span wire:loading wire:target="downloadPdf">Downloading…</span>
                            </button>
                            <button type="button" wire:click="closePreview" class="h-8 rounded-lg border border-slate-200 px-3 text-[12px] text-slate-600 hover:bg-slate-50">বন্ধ</button>
                        </div>
                    </div>

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
                            'financial_criteria' => $financial_criteria,
                            'vatObservationRows' => $vatObservationRows,
                            'taxObservationRows' => $taxObservationRows,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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