<?php

$h = file_get_contents(__DIR__.'/../storage/app/templates/audit/MF_Audit_Report_Format.html');
echo 'len='.strlen($h).PHP_EOL;
echo 'pagesections='.substr_count($h, 'word-page-sheet').PHP_EOL;
echo 'p_tags='.substr_count($h, '<p ').PHP_EOL;
$plain = trim(strip_tags($h));
echo 'plain_len='.mb_strlen($plain).PHP_EOL;
echo mb_substr($plain, 0, 800).PHP_EOL;

// Estimate pages: ~2200 chars printable per A4 page of Bengali
$est = max(1, (int) ceil(mb_strlen($plain) / 2200));
echo "estimated_pages=$est\n";

$pdf = file_get_contents(__DIR__.'/../storage/app/templates/audit/MF_Audit_Report_Format.pdf');
preg_match_all('/\/Type\s*\/Page[^s]/', $pdf, $m);
echo 'pdf_page_objects='.count($m[0]).PHP_EOL;
