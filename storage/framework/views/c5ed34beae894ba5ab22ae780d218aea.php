<?php
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

<table class="header-table" width="100%" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;table-layout:fixed;mso-table-layout-alt:fixed;">
    <tr>
        <td class="logo-cell" width="68%" valign="top" style="width:68%;padding-right:8pt;vertical-align:top;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($forDoc ?? false) && ! empty($logoDoc)): ?>
                <?php echo $__env->make('audits.partials.logo-word', ['logoDoc' => $logoDoc], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif(! empty($logoDataUri)): ?>
                <img src="<?php echo e($logoDataUri); ?>" class="logo-large" alt="Logo">
            <?php else: ?>
                <p class="org-name">DSK</p>
                <p class="org-bn">দুঃস্থ স্বাস্থ্য কেন্দ্র</p>
                <p class="org-en">Dushtha Shasthya Kendra</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <td class="rating-wrap" width="32%" valign="top" align="right" style="width:32%;max-width:108pt;vertical-align:top;text-align:right;overflow:hidden;">
            <table class="cover-rating" width="102" cellspacing="0" cellpadding="0" align="right"
                   style="width:102pt;max-width:102pt;border-collapse:collapse;table-layout:fixed;margin-left:auto;mso-table-layout-alt:fixed;">
                <tr>
                    <td class="cr-label" align="center" bgcolor="#1d4ed8"
                        style="background:#1d4ed8;color:#ffffff;font-size:7pt;font-weight:bold;line-height:1.2;padding:3pt 2pt;text-align:center;">
                        Branch Internal<br>Control Rating
                    </td>
                </tr>
                <tr>
                    <td class="cr-value" align="center" bgcolor="<?php echo e($ratingColor); ?>"
                        style="background:<?php echo e($ratingColor); ?>;border:1.5pt solid #f97316;color:#ffffff;font-size:8.5pt;font-weight:bold;line-height:1.2;padding:4pt 2pt;text-align:center;word-wrap:break-word;overflow-wrap:anywhere;">
                        <?php echo e($control_rating ?: '—'); ?>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="mt-4">
    <p><span class="bold">সূত্র নাম্বার:</span> <span class="dotted"><?php echo e($memo_no ?: '………………………………'); ?></span></p>
    <p><span class="bold">তারিখ:</span> <span class="dotted"><?php echo e($fmt($report_date)); ?></span></p>
</div>

<div class="mt-5">
    <p>বরাবর,</p>
    <p>যুগ্ম পরিচালক (নিরীক্ষা)</p>
    <p>দুঃস্থ স্বাস্থ্য কেন্দ্র (ডিএসকে)</p>
    <p>প্রধান কার্যালয়, ঢাকা।</p>
</div>

<h2>অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</h2>

<div>
    <p><span class="bold">শাখার নাম ও নাম্বার:</span> <span class="dotted"><?php echo e($shakha_display_name ?: '………………………………'); ?></span></p>
    <p><span class="bold">অঞ্চলের নাম:</span> <span class="dotted"><?php echo e($area_display_name ?: '………………………………'); ?></span></p>
    <p><span class="bold">নিরীক্ষাকাল:</span> <span class="dotted"><?php echo e($audit_period_label ?: '………………………………'); ?></span></p>
</div>

<div class="mt-4">
    <p class="bold">প্রিয় মহোদয়,</p>
    <p class="mt-2 justify">
        গত
        <span class="underline-field"><?php echo e($fmt($audit_start_date)); ?></span>
        হতে
        <span class="underline-field"><?php echo e($fmt($audit_end_date)); ?></span>
        পর্যন্ত মোট
        <span class="underline-field"><?php echo e($working_days !== null && $working_days !== '' ? $working_days : '……'); ?></span>
        কর্ম দিবস
        <span class="underline-field"><?php echo e($shakha_display_name ?: '………………'); ?></span>
        শাখা হতে
        <span class="underline-field"><?php echo e($period_scope ?: '………………'); ?></span>
        সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন
        <span class="underline-field"><?php echo e($fmt($draft_sent_date)); ?></span>
        ইং তারিখে প্রেরণ করা হয় এবং
        <span class="underline-field"><?php echo e($fmt($comments_received_date)); ?></span>
        তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।
    </p>
</div>

<div class="mt-6">
    <p>আপনার বিশ্বস্ত,</p>
    <p class="mt-5"><span class="bold">নাম:</span> <span class="dotted"><?php echo e($auditor_name ?: '……………………'); ?></span></p>
    <p><span class="bold">পদবী:</span> <span class="dotted"><?php echo e($auditor_designation ?: '……………………'); ?></span></p>
</div>

<div class="mt-6 copy-block">
    <p class="bold">অনুলিপি:</p>
    <ol class="copy">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($copy_recipients ?? \App\Support\AuditCopyRecipients::defaults()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <li><?php echo e($item); ?></li>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </ol>
</div>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/cover-page.blade.php ENDPATH**/ ?>