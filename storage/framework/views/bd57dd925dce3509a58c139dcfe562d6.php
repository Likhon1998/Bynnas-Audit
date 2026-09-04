<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($title ?? config('app.name', 'Bynnas Audit')); ?></title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
        <body class="font-sans text-[13px] font-normal leading-relaxed antialiased text-slate-700" x-data="{ sidebarOpen: false, searchOpen: false }" @keydown.window.prevent.ctrl.k="searchOpen = true" @keydown.window.escape="searchOpen = false; sidebarOpen = false">
        <?php if (isset($component)) { $__componentOriginalc5ad7eb21ddba49addb80e6944297ba0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-loader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0)): ?>
<?php $attributes = $__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0; ?>
<?php unset($__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc5ad7eb21ddba49addb80e6944297ba0)): ?>
<?php $component = $__componentOriginalc5ad7eb21ddba49addb80e6944297ba0; ?>
<?php unset($__componentOriginalc5ad7eb21ddba49addb80e6944297ba0); ?>
<?php endif; ?>
        <div class="flex h-screen overflow-hidden bg-canvas">
            <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="flex min-w-0 flex-1 flex-col">
                <?php echo $__env->make('layouts.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <main class="min-h-0 flex-1 overflow-y-auto">
                    <?php echo e($slot); ?>

                </main>
            </div>
        </div>

        <div
            x-show="searchOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/40 px-4 pt-24"
            @click.self="searchOpen = false"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl" @click.stop>
                <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    <input type="text" placeholder="Search keyword..." class="w-full border-0 p-0 text-[13px] text-slate-700 placeholder:text-slate-400 focus:ring-0" autofocus>
                </div>
                <div class="p-2">
                    <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">Dashboard</a>
                    <a href="<?php echo e(route('organogram')); ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">Organogram</a>
                    <a href="<?php echo e(route('shakhas.index')); ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">All Shakha</a>
                    <a href="<?php echo e(route('areas.index')); ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">All Areas</a>
                    <a href="<?php echo e(route('profile.edit')); ?>" class="flex items-center gap-3 rounded-lg px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50">Settings</a>
                </div>
            </div>
        </div>

        <?php echo $__env->yieldPushContent('scripts'); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\layouts\app.blade.php ENDPATH**/ ?>