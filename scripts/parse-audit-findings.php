<?php

$tmp = __DIR__.'/../storage/app/templates/audit/_xlsx_extract';
$outDir = __DIR__.'/../storage/app/templates/audit';

$sharedXml = file_get_contents($tmp.'/xl_sharedStrings.xml');
$shared = [];
if (preg_match_all('/<si>(.*?)<\/si>/s', $sharedXml, $siMatches)) {
    foreach ($siMatches[1] as $si) {
        if (preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $si, $tm)) {
            $shared[] = html_entity_decode(implode('', $tm[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
        } else {
            $shared[] = '';
        }
    }
}

file_put_contents($outDir.'/shared_strings.json', json_encode($shared, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'Shared count: '.count($shared)."\n";
echo "First 80 shared strings:\n";
foreach (array_slice($shared, 0, 80) as $i => $s) {
    echo sprintf("[%d] %s\n", $i, $s);
}

// Parse August-Sum with regex (small file)
$sheet = file_get_contents($tmp.'/sheet_1.xml');
$rows = [];
if (preg_match_all('/<c r="([A-Z]+)(\d+)"([^>]*)>(?:.*?<v>(.*?)<\/v>)?/s', $sheet, $cells, PREG_SET_ORDER)) {
    foreach ($cells as $cell) {
        $col = $cell[1];
        $row = (int) $cell[2];
        $attrs = $cell[3];
        $raw = $cell[4] ?? '';
        $val = $raw;
        if (str_contains($attrs, 't="s"') && $raw !== '') {
            $val = $shared[(int) $raw] ?? $raw;
        }
        $rows[$row][$col] = is_string($val) ? trim(preg_replace('/\s+/u', ' ', $val)) : $val;
    }
}

$findings = [];
foreach ($rows as $r => $cols) {
    if ($r === 1) {
        continue; // header
    }
    if (! empty($cols['A'])) {
        $findings[] = [
            'sl' => $r - 1,
            'title' => $cols['A'],
            'b' => $cols['B'] ?? null,
            'c' => $cols['C'] ?? null,
            'd' => $cols['D'] ?? null,
            'e' => $cols['E'] ?? null,
            'f' => $cols['F'] ?? null,
        ];
    }
}

file_put_contents($outDir.'/august_sum_findings.json', json_encode([
    'header' => $rows[1] ?? [],
    'findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\nAugust-Sum header:\n";
print_r($rows[1] ?? []);
echo 'Findings: '.count($findings)."\n";
foreach (array_slice($findings, 0, 60) as $f) {
    echo $f['sl'].'. '.$f['title']."\n";
}

// Header row from big sheet: read only first 2MB and capture row r="1"
$big = fopen($tmp.'/sheet_0.xml', 'rb');
$chunk = fread($big, 2 * 1024 * 1024);
fclose($big);
$headerCells = [];
if (preg_match_all('/<c r="([A-Z]+)(1)"([^>]*)>(?:.*?<v>(.*?)<\/v>)?/s', $chunk, $cells, PREG_SET_ORDER)) {
    foreach ($cells as $cell) {
        $col = $cell[1];
        $attrs = $cell[3];
        $raw = $cell[4] ?? '';
        $val = $raw;
        if (str_contains($attrs, 't="s"') && $raw !== '') {
            $val = $shared[(int) $raw] ?? $raw;
        }
        $headerCells[$col] = is_string($val) ? trim(preg_replace('/\s+/u', ' ', $val)) : $val;
    }
}

// Also try rows 2-4 for multi-header
for ($rowNum = 2; $rowNum <= 4; $rowNum++) {
    if (preg_match_all('/<c r="([A-Z]+)('.$rowNum.')"([^>]*)>(?:.*?<v>(.*?)<\/v>)?/s', $chunk, $cells, PREG_SET_ORDER)) {
        $line = [];
        foreach ($cells as $cell) {
            $col = $cell[1];
            $attrs = $cell[3];
            $raw = $cell[4] ?? '';
            $val = $raw;
            if (str_contains($attrs, 't="s"') && $raw !== '') {
                $val = $shared[(int) $raw] ?? $raw;
            }
            $line[$col] = is_string($val) ? trim(preg_replace('/\s+/u', ' ', $val)) : $val;
        }
        file_put_contents($outDir."/august_row{$rowNum}.json", json_encode($line, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Row $rowNum cells: ".count($line)."\n";
    }
}

file_put_contents($outDir.'/august_header_row1.json', json_encode($headerCells, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'Big sheet row1 cells: '.count($headerCells)."\n";
foreach ($headerCells as $col => $val) {
    echo "$col => $val\n";
}
