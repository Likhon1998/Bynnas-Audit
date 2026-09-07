<div class="a4-sheet">
    <div class="a4-inner official-preview text-black">
        <div class="a4-body">
            <?php echo $__env->make('livewire.partials.audit-signatures-block', [
                'sign_auditor_name' => $sign_auditor_name,
                'sign_auditor_designation' => $sign_auditor_designation,
                'sign_auditor_date' => $sign_auditor_date,
                'sign_bm_name' => $sign_bm_name,
                'sign_bm_date' => $sign_bm_date,
                'sign_abm_name' => $sign_abm_name,
                'sign_abm_date' => $sign_abm_date,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <p class="a4-page-num">5</p>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\livewire\partials\audit-page5-signatures-preview.blade.php ENDPATH**/ ?>