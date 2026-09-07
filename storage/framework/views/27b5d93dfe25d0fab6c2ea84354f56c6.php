<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</title>
    <style>
        body {
            font-family: hindsiliguri, sans-serif;
            font-size: 11pt;
            color: #111;
            line-height: 1.45;
        }
        img { max-width: 100%; }
        /* Only the cover forces a new sheet. Body packs tightly end-to-end. */
        .doc-cover { page-break-after: always; page-break-inside: avoid; }
        .doc-flow { page-break-inside: auto; }
        .section-follow { page-break-before: auto; page-break-inside: auto; margin-top: 4mm; }
        .page-num { display: none; }
    </style>
    <?php echo $__env->make('audits.partials.document-styles', ['isPdf' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>
<?php $dash = '………………'; ?>

<?php echo $__env->make('audits.partials.report-body', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\pdf.blade.php ENDPATH**/ ?>