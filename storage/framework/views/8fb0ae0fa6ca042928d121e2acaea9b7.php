<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title>Bynnas Audit</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="auth-canvas font-sans antialiased text-slate-800">
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
        <a href="<?php echo e(url('/')); ?>" class="absolute left-6 top-6 z-10 flex items-center gap-2.5">
            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-9 w-9']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-9 w-9']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
            <span class="text-lg font-semibold tracking-tight text-slate-800">Bynnas Audit</span>
        </a>

        <div class="flex min-h-screen items-center justify-center px-4 py-16">
            <div class="w-full max-w-[460px] rounded-2xl bg-white/90 p-8 shadow-[0_20px_60px_rgba(80,90,140,0.12)] backdrop-blur-md sm:p-10">
                <?php echo e($slot); ?>

            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/layouts/guest.blade.php ENDPATH**/ ?>