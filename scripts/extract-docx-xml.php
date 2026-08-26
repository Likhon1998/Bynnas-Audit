<?php

function extractDocxText(string $documentXmlPath): string
{
    $xml = file_get_contents($documentXmlPath);
    if ($xml === false) {
        return '';
    }

    // Keep paragraph breaks
    $xml = preg_replace('/<\/w:p>/', "\n", $xml);
    $xml = preg_replace('/<w:tab\/>/', "\t", $xml);
    $xml = preg_replace('/<w:br[^>]*\/>/', "\n", $xml);

    $text = strip_tags($xml);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    $text = preg_replace("/[ \t]+/", ' ', $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);

    return trim($text);
}

$base = __DIR__.'/../storage/app/templates/audit';

$mf = extractDocxText($base.'/mf_unzip/word/document.xml');
file_put_contents($base.'/MF_Audit_Report_Format_clean.txt', $mf);
fwrite(STDOUT, "MF length=".strlen($mf)."\n");
fwrite(STDOUT, substr($mf, 0, 4000)."\n----MF_END_SNIPPET----\n");

$dsk = extractDocxText($base.'/dsk_unzip/word/document.xml');
file_put_contents($base.'/DSK_clean.txt', $dsk);
fwrite(STDOUT, "DSK length=".strlen($dsk)."\n");
fwrite(STDOUT, substr($dsk, 0, 2000)."\n----DSK_END_SNIPPET----\n");
