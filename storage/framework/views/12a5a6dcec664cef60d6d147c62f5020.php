
<?php
    $editable = $editable ?? false;
?>
<div class="flex items-start justify-between gap-4">
    <div class="flex min-w-0 items-start gap-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
            <div class="shrink-0">
                <label class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'group relative flex cursor-pointer items-center justify-center overflow-hidden rounded border border-dashed border-slate-400 bg-slate-50 hover:border-[#2b579a] hover:bg-sky-50',
                    'h-auto min-h-[58px] w-auto max-w-[220px]' => ! empty($logoUrl),
                    'h-[58px] w-[58px]' => empty($logoUrl),
                ]); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($logoUrl)): ?>
                        <img src="<?php echo e($logoUrl); ?>" alt="Logo" class="max-h-[70px] max-w-[220px] object-contain p-0.5">
                    <?php else: ?>
                        <span class="px-1 text-center text-[9px] font-medium leading-tight text-slate-500">Add<br>Logo</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <input type="file" accept="image/*" wire:model="logoUpload" class="absolute inset-0 cursor-pointer opacity-0">
                </label>
                <div wire:loading wire:target="logoUpload" class="mt-0.5 text-[9px] text-slate-500">Uploading…</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logoUpload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-0.5 max-w-[70px] text-[9px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($logoUrl)): ?>
                    <button type="button" wire:click="removeLogo" class="mt-0.5 text-[9px] text-rose-600 hover:underline">Remove</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($logoUrl)): ?>
                <img src="<?php echo e($logoUrl); ?>" alt="Logo" class="max-h-[70px] max-w-[220px] shrink-0 object-contain">
            <?php else: ?>
                <div class="flex h-[58px] w-[58px] shrink-0 items-center justify-center overflow-hidden border border-slate-300 bg-white">
                    <span class="px-1 text-center text-[9px] leading-tight text-slate-400">Logo</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($logoUrl)): ?>
            <div class="leading-tight pt-0.5">
                <p class="text-[20px] font-extrabold tracking-tight text-black">DSK</p>
                <p class="text-[12px] font-semibold text-black">দুঃস্থ স্বাস্থ্য কেন্দ্র</p>
                <p class="text-[9px] font-semibold uppercase tracking-[0.04em] text-black">Dushtha Shasthya Kendra</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="w-[158px] shrink-0 text-center">
        <table class="w-full border-collapse" style="table-layout:fixed;">
            <tr>
                <td class="rounded-[2px] bg-[#1d4ed8] px-2 py-1.5 text-[10px] font-semibold leading-snug text-white">
                    Branch Internal<br>Control Rating
                </td>
            </tr>
            <tr>
                <td class="p-0 pt-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editable): ?>
                        <select
                            wire:model.live="control_rating"
                            class="w-full rounded-[2px] border-2 border-orange-400 px-1 py-1.5 text-center text-[11px] font-bold text-white"
                            style="background: <?php echo e($ratingColor); ?>;"
                        >
                            <option>Satisfactory</option>
                            <option>Minor</option>
                            <option>Medium</option>
                            <option>Major</option>
                            <option>Unsatisfactory</option>
                        </select>
                    <?php else: ?>
                        <div
                            class="w-full rounded-[2px] border-2 border-orange-400 px-2 py-2 text-center text-[11px] font-bold text-white"
                            style="background: <?php echo e($ratingColor); ?>;"
                        >
                            <?php echo e($control_rating ?: '—'); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-cover-letterhead.blade.php ENDPATH**/ ?>