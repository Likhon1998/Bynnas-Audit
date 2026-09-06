<?php
    $tabMonth = $month ?? (int) now()->month;
    $tabYear = $year ?? (int) now()->year;
    $activeTab = $activeTab ?? 'matrix';
?>

<nav class="mt-2 inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5" aria-label="Findings views">
    <a
        href="<?php echo e(route('audit-findings.index', ['month' => $tabMonth, 'year' => $tabYear])); ?>"
        class="rounded-md px-3 py-1.5 text-[12px] font-semibold transition <?php echo e($activeTab === 'matrix' ? 'bg-white text-navy-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'); ?>"
    >Findings Matrix</a>
    <a
        href="<?php echo e(route('audit-findings.summary', ['month' => $tabMonth, 'year' => $tabYear])); ?>"
        class="rounded-md px-3 py-1.5 text-[12px] font-semibold transition <?php echo e($activeTab === 'summary' ? 'bg-white text-navy-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'); ?>"
    >Findings Summary</a>
</nav>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audit-findings/partials/view-tabs.blade.php ENDPATH**/ ?>