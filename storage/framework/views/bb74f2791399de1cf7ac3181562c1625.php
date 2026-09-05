<?php echo $__env->make('audits.partials.document-styles', ['scope' => '.audit-doc-preview'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
    /* Professional A4 desk preview — isolate from Tailwind utilities */
    .audit-doc-preview {
        font-family: 'Hind Siliguri', 'Nirmala UI', Arial, sans-serif;
        color: #111;
        overflow-x: auto;
        -webkit-font-smoothing: antialiased;
    }
    .audit-doc-preview .sheet-label {
        width: 210mm;
        margin: 0 auto 8px;
        padding: 0 2px;
        color: #f8fafc !important;
        font-size: 11px !important;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: 0.01em;
    }
    .audit-doc-preview .page + .sheet-label {
        margin-top: 28px;
    }
    .audit-doc-preview .page {
        width: 210mm !important;
        min-width: 210mm !important;
        max-width: 210mm !important;
        min-height: 297mm !important;
        height: auto !important;
        margin: 0 auto 12mm;
        padding: 15mm 20mm 18mm !important;
        background: #fff !important;
        color: #111 !important;
        box-shadow:
            0 1px 1px rgba(0,0,0,.06),
            0 12px 28px rgba(0,0,0,.22);
        box-sizing: border-box !important;
        position: relative;
        overflow: visible;
        font-size: 11pt !important;
        line-height: 1.45 !important;
        display: block !important;
    }
    .audit-doc-preview .page-num {
        position: absolute !important;
        right: 20mm !important;
        bottom: 8mm !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 10pt !important;
        color: #111 !important;
        text-align: right !important;
        background: transparent !important;
        border: 0 !important;
    }

    /* Beat Tailwind .grid / utility collisions on document tables */
    .audit-doc-preview .page table.doc-table {
        display: table !important;
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .audit-doc-preview .page table.doc-table colgroup {
        display: table-column-group !important;
    }
    .audit-doc-preview .page table.doc-table col {
        display: table-column !important;
    }
    .audit-doc-preview .page table.doc-table thead {
        display: table-header-group !important;
    }
    .audit-doc-preview .page table.doc-table tbody {
        display: table-row-group !important;
    }
    .audit-doc-preview .page table.doc-table tr {
        display: table-row !important;
    }
    .audit-doc-preview .page table.doc-table th,
    .audit-doc-preview .page table.doc-table td {
        display: table-cell !important;
        float: none !important;
        position: static !important;
    }

    .audit-doc-preview .page table.sign-table {
        display: table !important;
        width: 100% !important;
        table-layout: fixed !important;
    }
    .audit-doc-preview .page table.sign-table tr { display: table-row !important; }
    .audit-doc-preview .page table.sign-table td { display: table-cell !important; }

    .audit-doc-preview .page table.header-table {
        display: table !important;
        width: 100% !important;
    }
    .audit-doc-preview .page table.header-table td {
        display: table-cell !important;
        border: none !important;
    }

    .audit-doc-preview .page table.cover-rating {
        display: table !important;
        width: 42mm !important;
        max-width: 42mm !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        margin-left: auto !important;
    }
    .audit-doc-preview .page table.cover-rating tr { display: table-row !important; }
    .audit-doc-preview .page table.cover-rating td {
        display: table-cell !important;
        float: none !important;
    }
    .audit-doc-preview .page.page-body {
        min-height: 297mm !important;
        height: auto !important;
        overflow: visible !important;
    }
    .audit-doc-preview .page .section-follow {
        margin-top: 6mm;
        clear: both;
    }
    .audit-doc-preview .page .financial-follow {
        margin-top: 8mm;
        padding-top: 3mm;
        border-top: 1px solid #ddd;
    }

    .audit-doc-preview .page h2,
    .audit-doc-preview .page h3 {
        font-family: inherit !important;
        color: #111 !important;
        text-align: center;
    }
    .audit-doc-preview .page .mt-2 { margin-top: 2mm !important; }
    .audit-doc-preview .page .mt-4 { margin-top: 4mm !important; }
    .audit-doc-preview .page .mt-5 { margin-top: 5mm !important; }
    .audit-doc-preview .page .mt-6 { margin-top: 6mm !important; }
    .audit-doc-preview .page .mt-8 { margin-top: 6mm !important; }
    .audit-doc-preview .page .bold { font-weight: 700 !important; }
    .audit-doc-preview .page .center { text-align: center !important; }
    .audit-doc-preview .page .left-align { text-align: left !important; }
    .audit-doc-preview .page .right-align { text-align: right !important; }
    .audit-doc-preview .page .justify { text-align: justify !important; }
    .audit-doc-preview .page ol.copy {
        list-style: decimal !important;
        margin: 1mm 0 0 7mm !important;
        padding: 0 !important;
    }
    .audit-doc-preview .page img.logo-large,
    .audit-doc-preview .page img {
        max-width: 62mm;
        max-height: 16mm;
        width: auto;
        height: auto;
        display: block;
    }
</style>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/livewire/partials/audit-document-preview-styles.blade.php ENDPATH**/ ?>