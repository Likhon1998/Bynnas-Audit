<?php

/**
 * Dump shared strings + header row from August-Sum (small) and first rows of main sheet via regex on a byte window.
 */
$base = __DIR__.'/../storage/app/templates/audit';
$extract = $base.'/_xlsx_extract';

$ssXml = file_get_contents($extract.'/xl_sharedStrings.xml');
$shared = [];
if (preg_match_all('/<si>(.*?)<\/si>/s', $ssXml, $siMatches)) {
    foreach ($siMatches[1] as $si) {
        if (preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $si, $tm)) {
            $shared[] = html_entity_decode(implode('', $tm[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
        } else {
            $shared[] = '';
        }
    }
}

file_put_contents($base.'/shared_strings.json', json_encode($shared, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'Shared strings: '.count($shared)."\n";
echo "First 80 shared strings:\n";
foreach (array_slice($shared, 0, 80) as $i => $s) {
    echo sprintf("[%d] %s\n", $i, $s);
}

// Parse August-Sum fully (small file)
$sum = file_get_contents($extract.'/sheet_1.xml');
$rows = [];
if (preg_match_all('/<c r="([A-Z]+)(\d+)"([^>]*)>(?:.*?<v>(.*?)<\/v>)?/s', $sum, $cells, PREG_SET_ORDER)) {
    foreach ($cells as $cell) {
        $col = $cell[1];
        $row = (int) $cell[2];
        $attrs = $cell[3];
        $raw = $cell[4] ?? '';
        $val = $raw;
        if (str_contains($attrs, 't="s"') && $raw !== '') {
            $val = $shared[(int) $raw] ?? $raw;
        }
        $rows[$row][$col] = $val;
    }
}
file_put_contents($base.'/august_sum_rows.json', json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nAugust-Sum rows parsed: ".count($rows)."\n";

// Read only first ~2MB of huge August sheet for header rows
$fh = fopen($extract.'/sheet_0.xml', 'rb');
$chunk = fread($fh, 2 * 1024 * 1024);
fclose($fh);

$headerRows = [];
if (preg_match_all('/<c r="([A-Z]+)(\d+)"([^>]*)>(?:.*?<v>(.*?)<\/v>)?/s', $chunk, $cells, PREG_SET_ORDER)) {
    foreach ($cells as $cell) {
        $row = (int) $cell[2];
        if ($row > 4) {
            continue;
        }
        $col = $cell[1];
        $attrs = $cell[3];
        $raw = $cell[4] ?? '';
        $val = $raw;
        if (str_contains($attrs, 't="s"') && $raw !== '') {
            $val = $shared[(int) $raw] ?? $raw;
        }
        $headerRows[$row][$col] = is_string($val) ? trim(preg_replace('/\s+/u', ' ', $val)) : $val;
    }
}

// Normalize to arrays ordered by column
function colIndex(string $col): int
{
    $n = 0;
    foreach (str_split(strtoupper($col)) as $ch) {
        $n = $n * 26 + (ord($ch) - 64);
    }

    return $n;
}
function indexCol(int $i): string
{
    $s = '';
    while ($i > 0) {
        $i--;
        $s = chr(65 + ($i % 26)).$s;
        $i = intdiv($i, 26);
    }

    return $s;
}

$normalized = [];
foreach ($headerRows as $r => $cols) {
    $max = 0;
    foreach (array_keys($cols) as $c) {
        $max = max($max, colIndex($c));
    }
    $line = [];
    for ($i = 1; $i <= $max; $i++) {
        $line[] = $cols[indexCol($i)] ?? null;
    }
    $normalized[$r] = $line;
}

file_put_contents($base.'/august_header_rows.json', json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "August header rows cols: ".count($normalized[1] ?? [])." / ".count($normalized[2] ?? [])." / ".count($normalized[3] ?? [])."\n";
if (! empty($normalized[1])) {
    echo "Row1 sample: ".implode(' | ', array_slice(array_map(fn ($v) => $v ?? '', $normalized[1]), 0, 25))."\n";
}
if (! empty($normalized[2])) {
    echo "Row2 sample: ".implode(' | ', array_slice(array_map(fn ($v) => $v ?? '', $normalized[2]), 0, 25))."\n";
}
if (! empty($normalized[3])) {
    echo "Row3 sample: ".implode(' | ', array_slice(array_map(fn ($v) => $v ?? '', $normalized[3]), 0, 25))."\n";
}

echo "DONE_OK\n";
