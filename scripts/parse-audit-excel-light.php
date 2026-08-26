<?php

ini_set('memory_limit', '1024M');

$path = __DIR__.'/../storage/app/templates/audit/AUDIT_FINDINGS_CONSOLIDATED_FORMAT.xlsx';
$outDir = __DIR__.'/../storage/app/templates/audit';
$tmp = $outDir.'/_xlsx_extract';

if (is_dir($tmp)) {
    // keep shared strings if present
} else {
    mkdir($tmp, 0777, true);
}

$zip = new ZipArchive;
if ($zip->open($path) !== true) {
    fwrite(STDERR, "Cannot open xlsx\n");
    exit(1);
}

// Extract only needed parts
foreach (['xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/sharedStrings.xml'] as $entry) {
    $data = $zip->getFromName($entry);
    if ($data !== false) {
        $dest = $tmp.'/'.str_replace('/', '_', $entry);
        file_put_contents($dest, $data);
    }
}

$workbook = file_get_contents($tmp.'/xl_workbook.xml');
$rels = file_get_contents($tmp.'/xl__rels_workbook.xml.rels');

preg_match_all('/<sheet[^>]*name="([^"]+)"[^>]*r:id="([^"]+)"/i', $workbook, $sheets, PREG_SET_ORDER);
preg_match_all('/Id="(rId\d+)"[^>]*Target="([^"]+)"/', $rels, $relMatches, PREG_SET_ORDER);
$relMap = [];
foreach ($relMatches as $m) {
    $relMap[$m[1]] = ltrim($m[2], '/');
}

// Load shared strings via XMLReader
$shared = [];
$ssPath = $tmp.'/xl_sharedStrings.xml';
if (is_file($ssPath)) {
    $reader = new XMLReader;
    $reader->open($ssPath);
    while ($reader->read()) {
        if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'si') {
            $node = $reader->readOuterXML();
            if (preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $node, $tm)) {
                $shared[] = html_entity_decode(implode('', $tm[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
            } else {
                $shared[] = '';
            }
        }
    }
    $reader->close();
}

echo 'Sheets='.count($sheets).' Shared='.count($shared)."\n";

$allHeaders = [];

foreach ($sheets as $index => $sheetMeta) {
    $name = html_entity_decode($sheetMeta[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
    $rid = $sheetMeta[2];
    $target = $relMap[$rid] ?? null;
    if (! $target) {
        continue;
    }
    if (! str_starts_with($target, 'xl/')) {
        $target = 'xl/'.$target;
    }

    // Stream extract sheet to temp file
    $stream = $zip->getStream($target);
    if ($stream === false) {
        echo "No stream $target\n";
        continue;
    }
    $localSheet = $tmp.'/sheet_'.$index.'.xml';
    $out = fopen($localSheet, 'wb');
    while (! feof($stream)) {
        fwrite($out, fread($stream, 1024 * 256));
    }
    fclose($out);
    fclose($stream);
    echo "Extracted $name -> $localSheet size=".filesize($localSheet)."\n";

    $rows = readFirstRows($localSheet, $shared, 5);
    $safe = preg_replace('/[^A-Za-z0-9_-]+/', '_', $name);
    file_put_contents($outDir."/excel_sheet_{$index}_{$safe}.json", json_encode([
        'name' => $name,
        'path' => $target,
        'col_count' => count($rows[0] ?? []),
        'rows' => $rows,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $header = [];
    foreach ($rows as $candidate) {
        if (count(array_filter($candidate, fn ($v) => $v !== null && $v !== '')) > count(array_filter($header))) {
            $header = $candidate;
        }
    }
    $allHeaders[$name] = $header;
    echo "Sheet[$index] $name headerCols=".count($header)."\n";
    // free memory
    unset($rows);
}

file_put_contents($outDir.'/excel_headers.json', json_encode($allHeaders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$zip->close();
echo "DONE\n";

/**
 * @param  list<string>  $shared
 * @return list<list<mixed>>
 */
function readFirstRows(string $sheetPath, array $shared, int $maxRow): array
{
    $rowsMap = [];
    $reader = new XMLReader;
    $reader->open($sheetPath);

    while ($reader->read()) {
        if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'c') {
            continue;
        }

        $ref = $reader->getAttribute('r') ?? '';
        if (! preg_match('/^([A-Z]+)(\d+)$/', $ref, $rm)) {
            continue;
        }
        $col = $rm[1];
        $rowNum = (int) $rm[2];
        if ($rowNum > $maxRow) {
            // once we are past max row in sheet order we can stop when row dimension jumps
            if ($rowNum > $maxRow + 20) {
                break;
            }
            continue;
        }

        $type = $reader->getAttribute('t');
        $inner = $reader->readInnerXML();
        $value = null;
        if (preg_match('/<v>(.*?)<\/v>/s', $inner, $vm)) {
            $raw = $vm[1];
            if ($type === 's') {
                $value = $shared[(int) $raw] ?? $raw;
            } else {
                $value = $raw;
            }
        } elseif (preg_match('/<t[^>]*>(.*?)<\/t>/s', $inner, $tm)) {
            $value = html_entity_decode($tm[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
        }

        if (is_string($value)) {
            $value = trim(preg_replace('/\s+/u', ' ', $value));
        }
        $rowsMap[$rowNum][$col] = $value;
    }
    $reader->close();

    ksort($rowsMap);
    $normalized = [];
    foreach ($rowsMap as $cols) {
        $maxIndex = 0;
        foreach (array_keys($cols) as $letters) {
            $maxIndex = max($maxIndex, colToIndex($letters));
        }
        $line = [];
        for ($i = 1; $i <= $maxIndex; $i++) {
            $line[] = $cols[indexToCol($i)] ?? null;
        }
        while ($line !== [] && ($line[array_key_last($line)] === null || $line[array_key_last($line)] === '')) {
            array_pop($line);
        }
        $normalized[] = $line;
    }

    return $normalized;
}

function colToIndex(string $col): int
{
    $col = strtoupper($col);
    $n = 0;
    for ($i = 0; $i < strlen($col); $i++) {
        $n = $n * 26 + (ord($col[$i]) - 64);
    }

    return $n;
}

function indexToCol(int $index): string
{
    $s = '';
    while ($index > 0) {
        $index--;
        $s = chr(65 + ($index % 26)).$s;
        $index = intdiv($index, 26);
    }

    return $s;
}
