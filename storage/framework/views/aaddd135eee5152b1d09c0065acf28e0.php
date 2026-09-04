<?php
    use App\Support\AuditDocumentLayout as Doc;
    $scope = $scope ?? '';
    $p = $scope !== '' ? $scope.' ' : '';
    $isPdf = $isPdf ?? false;
    $forDoc = $forDoc ?? false;
    $compact = $isPdf || $forDoc;
    $pad = Doc::pagePaddingCss();
?>
<style>
<?php if($compact): ?>
    <?php echo e($p); ?>.page,
    <?php echo e($p); ?>.doc-cover,
    <?php echo e($p); ?>.doc-flow,
    <?php echo e($p); ?>.doc-financial {
        width: auto;
        margin: 0;
        padding: 0;
        min-height: 0;
        height: auto;
        font-size: 11pt;
        line-height: 1.45;
        color: #111;
    }
    <?php echo e($p); ?>.page-num { display: none; }
    <?php echo e($p); ?>.signatures-follow { margin-top: 4mm; page-break-before: auto; }
    <?php echo e($p); ?>.section-follow { margin-top: 4mm; page-break-before: auto; }
    <?php echo e($p); ?>.financial-follow { margin-top: 5mm; }
<?php else: ?>
    <?php echo e($p); ?>.page {
        width: <?php echo e(Doc::PAGE_WIDTH_MM); ?>mm;
        min-height: <?php echo e(Doc::PAGE_HEIGHT_MM); ?>mm;
        padding: <?php echo e($pad); ?>;
        position: relative;
        font-size: 11pt;
        line-height: 1.45;
        color: #111;
        box-sizing: border-box;
    }
    <?php echo e($p); ?>.page-num {
        position: absolute;
        right: <?php echo e(Doc::MARGIN_RIGHT); ?>mm;
        bottom: <?php echo e(Doc::MARGIN_BOTTOM - 4); ?>mm;
        font-size: 10pt;
    }
    <?php echo e($p); ?>.signatures-follow { margin-top: 5mm; }
    <?php echo e($p); ?>.section-follow { margin-top: 5mm; }
    <?php echo e($p); ?>.financial-follow { margin-top: 6mm; }
<?php endif; ?>
    <?php echo e($p); ?>table {
        border-collapse: collapse;
        border-spacing: 0;
    }
    <?php echo e($p); ?>th,
    <?php echo e($p); ?>td { box-sizing: border-box; }

    <?php echo e($p); ?>.header-table { width: 100%; border: none; margin: 0; }
    <?php echo e($p); ?>.header-table td { border: none; padding: 0; vertical-align: top; }
    <?php echo e($p); ?>.row { width: 100%; }
    <?php echo e($p); ?>.left { float: left; }
    <?php echo e($p); ?>.right { float: right; }
    <?php echo e($p); ?>.clear { clear: both; }

    <?php echo e($p); ?>h2 {
        text-align: center;
        font-size: <?php echo e($compact ? '13pt' : '14pt'); ?>;
        font-weight: 700;
        text-decoration: underline;
        margin: <?php echo e($compact ? '3mm 0 3mm' : '5mm 0 4mm'); ?>;
    }
    <?php echo e($p); ?>h3 {
        text-align: center;
        font-size: <?php echo e($compact ? '12pt' : '12.5pt'); ?>;
        font-weight: 700;
        text-decoration: underline;
        margin: <?php echo e($compact ? '3mm 0 2mm' : '4mm 0 2.5mm'); ?>;
    }
    <?php echo e($p); ?>p { margin: 0 0 <?php echo e($compact ? '1.4mm' : '2mm'); ?>; }

    <?php echo e($p); ?>.mt-2 { margin-top: 2mm; }
    <?php echo e($p); ?>.mt-4 { margin-top: <?php echo e($compact ? '3mm' : '4mm'); ?>; }
    <?php echo e($p); ?>.mt-5 { margin-top: <?php echo e($compact ? '3.5mm' : '5mm'); ?>; }
    <?php echo e($p); ?>.mt-6 { margin-top: <?php echo e($compact ? '4mm' : '6mm'); ?>; }
    <?php echo e($p); ?>.mt-8 { margin-top: 6mm; }
    <?php echo e($p); ?>.bold { font-weight: 700; }
    <?php echo e($p); ?>.justify { text-align: justify; }
    <?php echo e($p); ?>.left-align { text-align: left; }
    <?php echo e($p); ?>.center { text-align: center; }
    <?php echo e($p); ?>.right-align { text-align: right; }

    <?php echo e($p); ?>.dotted {
        border-bottom: 1px dotted #111;
        padding: 0 2px 1px;
        font-weight: 700;
    }
    <?php echo e($p); ?>.underline-field {
        border-bottom: 1px dotted #111;
        padding: 0 3px;
        font-weight: 700;
    }

    <?php echo e($p); ?>table.doc-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 2mm;
        font-size: 9.5pt;
        <?php if($forDoc): ?>
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        <?php else: ?>
        display: table;
        <?php endif; ?>
    }
    <?php if(! $forDoc): ?>
    <?php echo e($p); ?>table.doc-table thead { display: table-header-group; }
    <?php echo e($p); ?>table.doc-table tbody { display: table-row-group; }
    <?php echo e($p); ?>table.doc-table tr { display: table-row; }
    <?php endif; ?>
    <?php echo e($p); ?>table.doc-table th,
    <?php echo e($p); ?>table.doc-table td {
        <?php if(! $forDoc): ?>
        display: table-cell;
        <?php endif; ?>
        border: 1px solid #222;
        <?php if($forDoc): ?>
        mso-border-alt: solid #222 0.5pt;
        <?php endif; ?>
        padding: <?php echo e($compact ? '1.1mm 1.4mm' : '1.6mm 2mm'); ?>;
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.35;
    }
    <?php echo e($p); ?>table.doc-table th {
        background: #d9d9d9;
        font-weight: 700;
        text-align: center;
        font-size: 8.5pt;
    }
    <?php echo e($p); ?>table.doc-table th.left-align { text-align: left; }
    <?php echo e($p); ?>table.doc-table .section {
        background: #efefef;
        font-weight: 700;
        text-align: left;
    }
    <?php echo e($p); ?>table.doc-table .align-top { vertical-align: top; }
    <?php echo e($p); ?>table.doc-table .rating-cell {
        padding: 0 !important;
        vertical-align: middle;
        width: 22%;
    }
    <?php echo e($p); ?>table.rating-box {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0;
        border: 0;
    }
    <?php echo e($p); ?>table.rating-box td {
        border: 1px solid #111;
        <?php if($forDoc): ?>
        mso-border-alt: solid #111 0.5pt;
        <?php endif; ?>
        padding: 1.1mm 0.8mm;
        text-align: center;
        font-weight: 700;
        line-height: 1.25;
        vertical-align: middle;
    }
    <?php echo e($p); ?>table.rating-box td.rb-head {
        background: #4472C4;
        color: #fff;
        font-size: 8pt;
    }
    <?php echo e($p); ?>table.rating-box td.rb-cell {
        background: #F8CBAD;
        color: #111;
        font-size: 9pt;
        width: 50%;
    }

    <?php echo e($p); ?>table.compact th,
    <?php echo e($p); ?>table.compact td { padding: <?php echo e($compact ? '1mm 1.2mm' : '1.2mm 1.5mm'); ?>; }

    <?php echo e($p); ?>table.glance-table td:nth-child(odd) { text-align: left; }
    <?php echo e($p); ?>table.glance-table td:nth-child(even) { text-align: center; }

    <?php echo e($p); ?>table.staff-table tbody td {
        font-size: 9pt;
        vertical-align: middle;
    }
    <?php echo e($p); ?>table.staff-table tbody td.left-align { text-align: left; }

    <?php echo e($p); ?>table.toc-table tbody td {
        font-size: 9pt;
        line-height: 1.35;
    }
    <?php echo e($p); ?>table.toc-table tbody td.align-top {
        vertical-align: top;
        text-align: left;
        padding-top: 1.4mm;
    }

    <?php echo e($p); ?>table.classification-table {
        font-size: <?php echo e($compact ? '7.5pt' : '8pt'); ?>;
        width: 100%;
    }
    <?php echo e($p); ?>table.classification-table th,
    <?php echo e($p); ?>table.classification-table td {
        font-size: inherit;
        line-height: 1.3;
        padding: 0.9mm 1.1mm;
    }
    <?php echo e($p); ?>table.finding-table { font-size: <?php echo e($compact ? '9.5pt' : '9.5pt'); ?>; }
    <?php echo e($p); ?>table.finding-table td { vertical-align: top; }
    <?php echo e($p); ?>table.finding-table td.body-cell { text-align: justify; }

    <?php echo e($p); ?>table.obs-table th {
        background: #2E5090;
        color: #fff;
        font-weight: 700;
        text-align: center;
        font-size: 8.5pt;
    }
    <?php echo e($p); ?>table.obs-table td {
        font-size: 8.5pt;
        text-align: center;
    }

    <?php echo e($p); ?>.section-heading {
        font-size: <?php echo e($compact ? '11pt' : '11pt'); ?>;
        font-weight: 700;
        margin: 0 0 2mm;
    }
    <?php echo e($p); ?>.obs-label {
        margin: 0 0 1mm;
        font-weight: 700;
        font-size: <?php echo e($compact ? '10pt' : '10pt'); ?>;
    }

    <?php echo e($p); ?>.page2-title {
        font-size: <?php echo e($compact ? '12pt' : '12pt'); ?>;
        font-weight: 700;
        margin: 0 0 2mm;
        text-align: left;
    }
    <?php echo e($p); ?>.page2-section { margin-top: 3.5mm; }

    /* Let সূচিপত্র continue across A4 pages instead of jumping to an empty sheet. */
    <?php echo e($p); ?>table.toc-table { page-break-inside: auto; }
    <?php if(! $forDoc): ?>
    <?php echo e($p); ?>table.toc-table thead { display: table-header-group; }
    <?php echo e($p); ?>table.toc-table tr { page-break-inside: avoid; page-break-after: auto; }
    <?php endif; ?>

    <?php echo e($p); ?>.logo-large {
        max-width: 62mm;
        max-height: 16mm;
        width: auto;
        height: auto;
        display: block;
    }
    <?php echo e($p); ?>.org-name { font-size: <?php echo e($compact ? '16pt' : '18pt'); ?>; font-weight: 700; margin: 0; line-height: 1.1; }
    <?php echo e($p); ?>.org-bn { font-size: <?php echo e($compact ? '11.5pt' : '11.5pt'); ?>; font-weight: 700; margin: 0; }
    <?php echo e($p); ?>.org-en {
        font-size: 8.5pt;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0;
        letter-spacing: 0.03em;
    }
    <?php echo e($p); ?>.rating-wrap {
        width: 42mm;
        text-align: center;
        vertical-align: top;
    }
    <?php echo e($p); ?>table.cover-rating {
        width: 42mm;
        max-width: 42mm;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0 0 0 auto;
        border: 0;
    }
    <?php echo e($p); ?>table.cover-rating td {
        border: 0;
        padding: 0;
        text-align: center;
        vertical-align: middle;
    }
    <?php echo e($p); ?>table.cover-rating td.cr-label {
        background: #1d4ed8;
        color: #fff;
        font-size: 8pt;
        font-weight: 700;
        line-height: 1.25;
        padding: 2mm 1.5mm;
    }
    <?php echo e($p); ?>table.cover-rating td.cr-value {
        border: 2px solid #f97316;
        color: #fff;
        font-size: 10pt;
        font-weight: 700;
        line-height: 1.3;
        padding: 2.2mm 1.5mm;
    }
    /* legacy class names kept for editor letterhead */
    <?php echo e($p); ?>.rating-label {
        background: #1d4ed8;
        color: #fff;
        font-size: 8.5pt;
        font-weight: 700;
        padding: 1.5mm 2mm;
        line-height: 1.25;
    }
    <?php echo e($p); ?>.rating-value {
        margin-top: 1mm;
        border: 2px solid #f97316;
        color: #fff;
        font-size: 10pt;
        font-weight: 700;
        padding: 2mm;
    }

    <?php echo e($p); ?>.sign-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 3mm;
    }
    <?php echo e($p); ?>.sign-table td {
        width: 33.33%;
        border: 1px solid #222;
        <?php if($forDoc): ?>
        mso-border-alt: solid #222 0.5pt;
        <?php endif; ?>
        vertical-align: top;
        padding: 2.2mm;
        font-size: 9.5pt;
        height: auto;
        min-height: 18mm;
        text-align: left;
    }
    <?php echo e($p); ?>.sign-table { page-break-inside: avoid; page-break-after: avoid; }

    <?php echo e($p); ?>table.finding-table {
        width: 100%;
        page-break-inside: avoid;
    }
    <?php echo e($p); ?>table.obs-table {
        width: 100%;
        page-break-inside: avoid;
    }

    <?php echo e($p); ?>.classification-section { margin-top: 3mm; page-break-inside: auto; }
    <?php echo e($p); ?>table.classification-table { page-break-inside: auto; }
    <?php echo e($p); ?>table.classification-summary {
        margin-top: 2.5mm;
        page-break-inside: avoid;
        page-break-before: avoid;
        page-break-after: avoid;
    }
    <?php echo e($p); ?>table.classification-summary th {
        width: auto;
    }
    <?php echo e($p); ?>ol.copy { margin: 1mm 0 0 6mm; padding: 0; list-style: decimal; }
    <?php echo e($p); ?>ol.copy li { margin: <?php echo e($compact ? '0.4mm' : '0.8mm'); ?> 0; }
    <?php echo e($p); ?>.copy-block { page-break-inside: avoid; }

    <?php
        // PDF: Hind Siliguri + OTL (mPDF-safe). Noto Sans Bengali crashes mPDF with GPOS Format 3 when OTL is on,
        // and without OTL Bengali digits (esp. ১) look broken — so PDF must not use Noto.
        $bnFont = $forDoc
            ? "'Nirmala UI', 'Vrinda', 'Kalpurush', 'Segoe UI', sans-serif"
            : ($isPdf
                ? 'hindsiliguri, sans-serif'
                : "'Noto Sans Bengali', 'Hind Siliguri', 'Nirmala UI', sans-serif");
    ?>
    <?php echo e($p); ?>.bn-num {
        font-family: <?php echo e($bnFont); ?>;
        <?php if($isPdf && ! $forDoc): ?>
            letter-spacing: 0;
        <?php else: ?>
            font-variant-numeric: tabular-nums lining-nums;
            font-feature-settings: "tnum" 1, "lnum" 1;
            letter-spacing: 0.05em;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        <?php endif; ?>
    }
    <?php if($isPdf && ! $forDoc): ?>
    <?php echo e($p); ?>body,
    <?php echo e($p); ?>.finding-heading,
    <?php echo e($p); ?>.section-heading,
    <?php echo e($p); ?>td,
    <?php echo e($p); ?>th,
    <?php echo e($p); ?>p,
    <?php echo e($p); ?>span {
        font-family: hindsiliguri, sans-serif;
    }
    <?php echo e($p); ?>.bn-serial,
    <?php echo e($p); ?>.bn-serial-section,
    <?php echo e($p); ?>.bn-page,
    <?php echo e($p); ?>.bn-index,
    <?php echo e($p); ?>.bn-stat {
        letter-spacing: 0;
    }
    <?php endif; ?>
    <?php echo e($p); ?>.bn-serial {
        display: inline-block;
        min-width: 2.2em;
        font-weight: 700;
        font-size: 9.5pt;
        color: #1e3a5f;
        line-height: 1.25;
    }
    <?php echo e($p); ?>.bn-serial-section {
        display: inline-block;
        min-width: 2.4em;
        font-weight: 700;
        font-size: 10pt;
        color: #0f172a;
        letter-spacing: 0.07em;
        line-height: 1.25;
    }
    <?php echo e($p); ?>.bn-page {
        display: inline-block;
        min-width: 1.4em;
        font-weight: 700;
        font-size: 9.5pt;
        color: #1d4ed8;
        line-height: 1.25;
    }
    <?php echo e($p); ?>a.bn-page-link {
        color: #1d4ed8;
        text-decoration: none;
    }
    <?php echo e($p); ?>a.bn-page-link .bn-page {
        border-bottom: 1px solid #93c5fd;
        padding-bottom: 0.5pt;
    }
    <?php echo e($p); ?>.bn-index {
        display: inline-block;
        min-width: 1.6em;
        font-weight: 700;
        font-size: 9.5pt;
        color: #334155;
        line-height: 1.25;
    }
    <?php echo e($p); ?>.bn-stat {
        font-weight: 700;
        font-size: 9.5pt;
        color: #0f172a;
        letter-spacing: 0.04em;
    }
    <?php echo e($p); ?>table.doc-table td:first-child .bn-num,
    <?php echo e($p); ?>table.doc-table th:first-child .bn-num {
        text-align: center;
    }
</style>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/document-styles.blade.php ENDPATH**/ ?>