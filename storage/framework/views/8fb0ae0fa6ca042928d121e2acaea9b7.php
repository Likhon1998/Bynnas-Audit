<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title>Bynnas Audit</title>
        <link rel="icon" type="image/png" href="<?php echo e(asset('images/bynnas-logo.png')); ?>?v=3">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="auth-canvas font-sans antialiased text-slate-800 h-screen overflow-hidden">
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
        <a href="<?php echo e(url('/')); ?>" class="absolute left-5 top-4 z-10 flex items-center gap-2">
            <img
                src="<?php echo e(asset('images/bynnas-logo.png')); ?>?v=3"
                alt="Bynnas"
                class="h-9 w-9 object-contain"
            >
            <span class="text-[13px] font-semibold tracking-tight text-slate-800">Bynnas Audit</span>
        </a>

        <div class="flex h-screen items-center justify-center overflow-hidden px-4 py-6">
            <div class="w-full max-w-[400px] rounded-2xl bg-white/95 px-6 py-6 shadow-[0_16px_40px_rgba(80,90,140,0.12)] backdrop-blur-md sm:px-7 sm:py-7">
                <?php echo e($slot); ?>

            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/layouts/guest.blade.php ENDPATH**/ ?>