<?php
    $dash = '………………';
    $fmt = function (?string $date) {
        if (! $date) {
            return '……………………';
        }
        $date = trim($date);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y'] as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $date)->format('d/m/Y');
            } catch (\Throwable) {
                // try next
            }
        }
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    };
?>

<div class="grid grid-cols-3 gap-[3mm] text-[10.5px] leading-[1.45]">
    <div class="min-h-[28mm] border border-black p-[2.5mm]">
        <p>নিরীক্ষা কর্মকর্তার নাম: <span class="font-semibold"><?php echo e($sign_auditor_name !== '' ? $sign_auditor_name : $dash); ?></span></p>
        <p class="mt-[2.5mm]">পদবী: <span class="font-semibold"><?php echo e($sign_auditor_designation !== '' ? $sign_auditor_designation : $dash); ?></span></p>
        <p class="mt-[2.5mm]">তারিখ: <span class="font-semibold"><?php echo e($fmt($sign_auditor_date)); ?></span></p>
    </div>
    <div class="min-h-[28mm] border border-black p-[2.5mm]">
        <p>শাখা ব্যবস্থাপকের নাম: <span class="font-semibold"><?php echo e($sign_bm_name !== '' ? $sign_bm_name : $dash); ?></span></p>
        <p class="mt-[8mm]">তারিখ: <span class="font-semibold"><?php echo e($fmt($sign_bm_date)); ?></span></p>
    </div>
    <div class="min-h-[28mm] border border-black p-[2.5mm]">
        <p>সহকারী শাখা ব্যবস্থাপকের নাম: <span class="font-semibold"><?php echo e($sign_abm_name !== '' ? $sign_abm_name : $dash); ?></span></p>
        <p class="mt-[8mm]">তারিখ: <span class="font-semibold"><?php echo e($fmt($sign_abm_date)); ?></span></p>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-signatures-block.blade.php ENDPATH**/ ?>