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

    <style>main { overflow: hidden !important; }</style>

    <?php
        $user = auth()->user();
        $initials = collect(explode(' ', trim((string) $user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        if ($initials === '') {
            $initials = 'BA';
        }
    ?>

    <div class="flex h-full min-h-0 flex-col overflow-hidden px-3 py-2 lg:px-4">
        <div class="mx-auto flex min-h-0 w-full max-w-5xl flex-1 flex-col gap-2 overflow-hidden">
            <div class="shrink-0 overflow-hidden rounded-xl border border-[#e9d5ff]/60 bg-white/95 shadow-sm">
                <div class="h-1 bg-gradient-to-r from-[#ff2d9b] via-[#7c3aed] to-[#2563eb]"></div>
                <div class="flex items-center gap-2.5 px-3 py-2">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#ff2d9b] via-[#7c3aed] to-[#2563eb] text-[11px] font-bold text-white">
                        <?php echo e($initials); ?>

                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-[13px] font-semibold tracking-tight text-navy-900"><?php echo e($user->name); ?></h1>
                        <p class="truncate text-[10px] text-slate-500">
                            <?php echo e($user->email); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($user, 'roleLabel')): ?>
                                · <?php echo e($user->roleLabel()); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                    <p class="hidden text-[10px] text-slate-400 sm:block">Account settings</p>
                </div>
            </div>

            <div class="grid min-h-0 flex-1 gap-2 overflow-hidden lg:grid-cols-2">
                <div class="min-h-0 overflow-hidden rounded-xl border border-[#e9d5ff]/50 bg-gradient-to-br from-[#fff7fb] via-white to-[#eef4ff] p-3 shadow-sm">
                    <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="min-h-0 overflow-hidden rounded-xl border border-[#c7d2fe]/50 bg-gradient-to-br from-[#f5f3ff] via-white to-[#eff6ff] p-3 shadow-sm">
                    <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="shrink-0 overflow-hidden rounded-xl border border-rose-200/50 bg-gradient-to-r from-rose-50/90 via-white to-[#fff1f8] px-3 py-2 shadow-sm">
                <?php echo $__env->make('profile.partials.delete-user-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\profile\edit.blade.php ENDPATH**/ ?>