<?php

/**
 * Convert MF Audit Report Format.docx (document.xml) into clean editable HTML pages.
 */
$xmlPath = __DIR__.'/../storage/app/templates/audit/mf_unzip/word/document.xml';
$outPath = __DIR__.'/../storage/app/templates/audit/MF_Audit_Report_Format.html';

if (! is_file($xmlPath)) {
    fwrite(STDERR, "Missing document.xml\n");
    exit(1);
}

$xml = file_get_contents($xmlPath);

// Soft page breaks / section breaks → page markers
$xml = preg_replace('/<w:br[^>]*w:type="page"[^>]*\/>/', '[[PAGE_BREAK]]', $xml);
$xml = preg_replace('/<w:lastRenderedPageBreak\/>/', '[[PAGE_BREAK]]', $xml);

// Paragraphs
$html = preg_replace_callback('/<w:p[\s>](.*?)<\/w:p>/s', function ($m) {
    $p = $m[0];
    $align = 'left';
    if (preg_match('/w:val="center"/', $p)) {
        $align = 'center';
    } elseif (preg_match('/w:val="right"/', $p)) {
        $align = 'right';
    } elseif (preg_match('/w:val="both"/', $p)) {
        $align = 'justify';
    }

    $bold = str_contains($p, '<w:b/>') || str_contains($p, '<w:b ');
    $underline = str_contains($p, '<w:u ');

    $texts = [];
    if (preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $p, $tm)) {
        foreach ($tm[1] as $t) {
            $texts[] = html_entity_decode($t, ENT_QUOTES | ENT_XML1, 'UTF-8');
        }
    }
    $line = trim(implode('', $texts));
    if ($line === '' && ! str_contains($p, '[[PAGE_BREAK]]')) {
        return '<p style="margin:0 0 8px;">&nbsp;</p>';
    }
    if (str_contains($p, '[[PAGE_BREAK]]') && $line === '') {
        return '[[PAGE_BREAK]]';
    }

    $style = 'margin:0 0 8px;text-align:'.$align.';';
    if ($bold) {
        $line = '<strong>'.e_keep($line).'</strong>';
    } else {
        $line = e_keep($line);
    }
    if ($underline) {
        $line = '<u>'.$line.'</u>';
    }

    return '<p style="'.$style.'">'.$line.'</p>';
}, $xml);

$html = strip_tags($html, '<p><strong><u><br><em>');
$html = preg_replace('/\s*\[\[PAGE_BREAK\]\]\s*/', '[[PAGE_BREAK]]', $html);

$pages = preg_split('/\[\[PAGE_BREAK\]\]/', $html);
$pages = array_values(array_filter(array_map('trim', $pages), fn ($p) => $p !== ''));

$doc = '<div class="dsk-audit-doc" style="font-family:\'Hind Siliguri\',Arial,sans-serif;font-size:14px;line-height:1.7;color:#111;">';
foreach ($pages as $i => $pageHtml) {
    $n = $i + 1;
    $doc .= '<section class="word-page-sheet" data-page="'.$n.'" contenteditable="true" style="background:#fff;min-height:1000px;padding:56px 64px;margin:0 0 24px;box-shadow:0 2px 12px rgba(0,0,0,.18);page-break-after:always;">';
    $doc .= '<div style="text-align:right;color:#999;font-size:11px;margin-bottom:8px;">পৃষ্ঠা '.$n.' / '.count($pages).'</div>';
    $doc .= $pageHtml;
    $doc .= '</section>';
}
$doc .= '</div>';

file_put_contents($outPath, $doc);
file_put_contents(__DIR__.'/../storage/app/templates/audit/MF_page_count.txt', (string) count($pages));
echo 'Pages: '.count($pages).' HTML bytes: '.strlen($doc)."\n";

function e_keep(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
