@php
    use App\Support\AuditDocumentLayout as Doc;
    $scope = $scope ?? '';
    $p = $scope !== '' ? $scope.' ' : '';
    $isPdf = $isPdf ?? false;
    $compact = $isPdf;
    $pad = Doc::pagePaddingCss();
@endphp
<style>
@if ($compact)
    {{ $p }}.page,
    {{ $p }}.doc-cover,
    {{ $p }}.doc-flow,
    {{ $p }}.doc-financial {
        width: auto;
        margin: 0;
        padding: 0;
        min-height: 0;
        height: auto;
        font-size: 11pt;
        line-height: 1.45;
        color: #111;
    }
    {{ $p }}.page-num { display: none; }
    {{ $p }}.signatures-follow { margin-top: 4mm; page-break-before: auto; }
    {{ $p }}.section-follow { margin-top: 4mm; page-break-before: auto; }
    {{ $p }}.financial-follow { margin-top: 5mm; }
@else
    {{ $p }}.page {
        width: {{ Doc::PAGE_WIDTH_MM }}mm;
        min-height: {{ Doc::PAGE_HEIGHT_MM }}mm;
        padding: {{ $pad }};
        position: relative;
        font-size: 11pt;
        line-height: 1.45;
        color: #111;
        box-sizing: border-box;
    }
    {{ $p }}.page-num {
        position: absolute;
        right: {{ Doc::MARGIN_RIGHT }}mm;
        bottom: {{ Doc::MARGIN_BOTTOM - 4 }}mm;
        font-size: 10pt;
    }
    {{ $p }}.signatures-follow { margin-top: 5mm; }
    {{ $p }}.section-follow { margin-top: 5mm; }
    {{ $p }}.financial-follow { margin-top: 6mm; }
@endif
    {{ $p }}table {
        border-collapse: collapse;
        border-spacing: 0;
    }
    {{ $p }}th,
    {{ $p }}td { box-sizing: border-box; }

    {{ $p }}.header-table { width: 100%; border: none; margin: 0; }
    {{ $p }}.header-table td { border: none; padding: 0; vertical-align: top; }
    {{ $p }}.row { width: 100%; }
    {{ $p }}.left { float: left; }
    {{ $p }}.right { float: right; }
    {{ $p }}.clear { clear: both; }

    {{ $p }}h2 {
        text-align: center;
        font-size: {{ $compact ? '13pt' : '14pt' }};
        font-weight: 700;
        text-decoration: underline;
        margin: {{ $compact ? '3mm 0 3mm' : '5mm 0 4mm' }};
    }
    {{ $p }}h3 {
        text-align: center;
        font-size: {{ $compact ? '12pt' : '12.5pt' }};
        font-weight: 700;
        text-decoration: underline;
        margin: {{ $compact ? '3mm 0 2mm' : '4mm 0 2.5mm' }};
    }
    {{ $p }}p { margin: 0 0 {{ $compact ? '1.4mm' : '2mm' }}; }

    {{ $p }}.mt-2 { margin-top: 2mm; }
    {{ $p }}.mt-4 { margin-top: {{ $compact ? '3mm' : '4mm' }}; }
    {{ $p }}.mt-5 { margin-top: {{ $compact ? '3.5mm' : '5mm' }}; }
    {{ $p }}.mt-6 { margin-top: {{ $compact ? '4mm' : '6mm' }}; }
    {{ $p }}.mt-8 { margin-top: 6mm; }
    {{ $p }}.bold { font-weight: 700; }
    {{ $p }}.justify { text-align: justify; }
    {{ $p }}.left-align { text-align: left; }
    {{ $p }}.center { text-align: center; }
    {{ $p }}.right-align { text-align: right; }

    {{ $p }}.dotted {
        border-bottom: 1px dotted #111;
        padding: 0 2px 1px;
        font-weight: 700;
    }
    {{ $p }}.underline-field {
        border-bottom: 1px dotted #111;
        padding: 0 3px;
        font-weight: 700;
    }

    {{ $p }}table.doc-table {
        display: table;
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 2mm;
        font-size: 9.5pt;
    }
    {{ $p }}table.doc-table thead { display: table-header-group; }
    {{ $p }}table.doc-table tbody { display: table-row-group; }
    {{ $p }}table.doc-table tr { display: table-row; }
    {{ $p }}table.doc-table th,
    {{ $p }}table.doc-table td {
        display: table-cell;
        border: 1px solid #222;
        padding: {{ $compact ? '1.1mm 1.4mm' : '1.6mm 2mm' }};
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.35;
    }
    {{ $p }}table.doc-table th {
        background: #d9d9d9;
        font-weight: 700;
        text-align: center;
        font-size: 8.5pt;
    }
    {{ $p }}table.doc-table th.left-align { text-align: left; }
    {{ $p }}table.doc-table .section {
        background: #efefef;
        font-weight: 700;
        text-align: left;
    }
    {{ $p }}table.doc-table .align-top { vertical-align: top; }
    {{ $p }}table.doc-table .rating-cell {
        padding: 0 !important;
        vertical-align: middle;
        width: 22%;
    }
    {{ $p }}table.rating-box {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0;
        border: 0;
    }
    {{ $p }}table.rating-box td {
        border: 1px solid #111;
        padding: 1.1mm 0.8mm;
        text-align: center;
        font-weight: 700;
        line-height: 1.25;
        vertical-align: middle;
    }
    {{ $p }}table.rating-box td.rb-head {
        background: #4472C4;
        color: #fff;
        font-size: 8pt;
    }
    {{ $p }}table.rating-box td.rb-cell {
        background: #F8CBAD;
        color: #111;
        font-size: 9pt;
        width: 50%;
    }

    {{ $p }}table.compact th,
    {{ $p }}table.compact td { padding: {{ $compact ? '1mm 1.2mm' : '1.2mm 1.5mm' }}; }

    {{ $p }}table.glance-table td:nth-child(odd) { text-align: left; }
    {{ $p }}table.glance-table td:nth-child(even) { text-align: center; }

    {{ $p }}table.staff-table tbody td {
        font-size: 9pt;
        vertical-align: middle;
    }
    {{ $p }}table.staff-table tbody td.left-align { text-align: left; }

    {{ $p }}table.toc-table tbody td {
        font-size: 9pt;
        line-height: 1.35;
    }
    {{ $p }}table.toc-table tbody td.align-top {
        vertical-align: top;
        text-align: left;
        padding-top: 1.4mm;
    }

    {{ $p }}table.classification-table {
        font-size: {{ $compact ? '7.5pt' : '8pt' }};
        width: 100%;
    }
    {{ $p }}table.classification-table th,
    {{ $p }}table.classification-table td {
        font-size: inherit;
        line-height: 1.3;
        padding: 0.9mm 1.1mm;
    }
    {{ $p }}table.finding-table { font-size: {{ $compact ? '9.5pt' : '9.5pt' }}; }
    {{ $p }}table.finding-table td { vertical-align: top; }
    {{ $p }}table.finding-table td.body-cell { text-align: justify; }

    {{ $p }}table.obs-table th {
        background: #2E5090;
        color: #fff;
        font-weight: 700;
        text-align: center;
        font-size: 8.5pt;
    }
    {{ $p }}table.obs-table td {
        font-size: 8.5pt;
        text-align: center;
    }

    {{ $p }}.section-heading {
        font-size: {{ $compact ? '11pt' : '11pt' }};
        font-weight: 700;
        margin: 0 0 2mm;
    }
    {{ $p }}.obs-label {
        margin: 0 0 1mm;
        font-weight: 700;
        font-size: {{ $compact ? '10pt' : '10pt' }};
    }

    {{ $p }}.page2-title {
        font-size: {{ $compact ? '12pt' : '12pt' }};
        font-weight: 700;
        margin: 0 0 2mm;
        text-align: left;
    }
    {{ $p }}.page2-section { margin-top: 3.5mm; }

    /* Let সূচিপত্র continue across A4 pages instead of jumping to an empty sheet. */
    {{ $p }}table.toc-table { page-break-inside: auto; }
    {{ $p }}table.toc-table thead { display: table-header-group; }
    {{ $p }}table.toc-table tr { page-break-inside: avoid; page-break-after: auto; }

    {{ $p }}.logo-large {
        max-width: 62mm;
        max-height: 16mm;
        width: auto;
        height: auto;
        display: block;
    }
    {{ $p }}.org-name { font-size: {{ $compact ? '16pt' : '18pt' }}; font-weight: 700; margin: 0; line-height: 1.1; }
    {{ $p }}.org-bn { font-size: {{ $compact ? '11.5pt' : '11.5pt' }}; font-weight: 700; margin: 0; }
    {{ $p }}.org-en {
        font-size: 8.5pt;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0;
        letter-spacing: 0.03em;
    }
    {{ $p }}.rating-wrap {
        width: 42mm;
        text-align: center;
        vertical-align: top;
    }
    {{ $p }}table.cover-rating {
        width: 42mm;
        max-width: 42mm;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0 0 0 auto;
        border: 0;
    }
    {{ $p }}table.cover-rating td {
        border: 0;
        padding: 0;
        text-align: center;
        vertical-align: middle;
    }
    {{ $p }}table.cover-rating td.cr-label {
        background: #1d4ed8;
        color: #fff;
        font-size: 8pt;
        font-weight: 700;
        line-height: 1.25;
        padding: 2mm 1.5mm;
    }
    {{ $p }}table.cover-rating td.cr-value {
        border: 2px solid #f97316;
        color: #fff;
        font-size: 10pt;
        font-weight: 700;
        line-height: 1.3;
        padding: 2.2mm 1.5mm;
    }
    /* legacy class names kept for editor letterhead */
    {{ $p }}.rating-label {
        background: #1d4ed8;
        color: #fff;
        font-size: 8.5pt;
        font-weight: 700;
        padding: 1.5mm 2mm;
        line-height: 1.25;
    }
    {{ $p }}.rating-value {
        margin-top: 1mm;
        border: 2px solid #f97316;
        color: #fff;
        font-size: 10pt;
        font-weight: 700;
        padding: 2mm;
    }

    {{ $p }}.sign-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 3mm;
    }
    {{ $p }}.sign-table td {
        width: 33.33%;
        border: 1px solid #222;
        vertical-align: top;
        padding: 2.2mm;
        font-size: 9.5pt;
        height: auto;
        min-height: 18mm;
        text-align: left;
    }
    {{ $p }}.sign-table { page-break-inside: avoid; page-break-after: avoid; }

    {{ $p }}table.finding-table {
        width: 100%;
        page-break-inside: avoid;
    }
    {{ $p }}table.obs-table {
        width: 100%;
        page-break-inside: avoid;
    }

    {{ $p }}.classification-section { margin-top: 3mm; page-break-inside: auto; }
    {{ $p }}table.classification-table { page-break-inside: auto; }
    {{ $p }}table.classification-summary {
        margin-top: 2.5mm;
        page-break-inside: avoid;
        page-break-before: avoid;
        page-break-after: avoid;
    }
    {{ $p }}table.classification-summary th {
        width: auto;
    }
    {{ $p }}ol.copy { margin: 1mm 0 0 6mm; padding: 0; list-style: decimal; }
    {{ $p }}ol.copy li { margin: {{ $compact ? '0.4mm' : '0.8mm' }} 0; }
    {{ $p }}.copy-block { page-break-inside: avoid; }
</style>
