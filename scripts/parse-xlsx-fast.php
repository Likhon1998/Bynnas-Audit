<?php

/**
 * Fast header extract from huge xlsx without loading whole workbook into memory.
 * Reads sharedStrings + first worksheet sheetData (first N rows).
 */
$xlsx = $argv[1] ?? __DIR__.'/../storage/app/templates/audit/AUDIT_FINDINGS_CONSOLIDATED_FORMAT.xlsx';
$out = $argv[2] ?? __DIR__.'/../storage/app/templates/audit/excel_headers.json';
$maxRows = (int) ($argv[3] ?? 8);

$zip = new ZipArchive();
if ($zip->open($xlsx) !== true) {
    fwrite(STDERR, "Cannot open $xlsx\n");
    exit(1);
}

$shared = [];
$ssXml = $zip->getFromName('xl/sharedStrings.xml');
if ($ssXml !== false) {
    $sx = @simplexml_load_string($ssXml);
    if ($sx) {
        foreach ($sx->si as $si) {
            if (isset($si->t)) {
                $shared[] = trim((string) $si->t);
            } else {
                $parts = [];
                foreach ($si->r as $r) {
                    $parts[] = (string) $r->t;
                }
                $shared[] = trim(implode('', $parts));
            }
        }
    }
}

$wb = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
$wb->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
$rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
$relMap = [];
foreach ($rels->Relationship as $rel) {
    $relMap[(string) $rel['Id']] = (string) $rel['Target'];
}

$sheetsMeta = [];
foreach ($wb->sheets->sheet as $sheet) {
    $name = (string) $sheet['name'];
    $rid = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
    $target = $relMap[$rid] ?? null;
    if (! $target) {
        continue;
    }
    $path = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/'.$target;
    // normalize xl/worksheets/sheet1.xml
    if (! str_starts_with($path, 'xl/')) {
        $path = 'xl/'.$path;
    }
    $sheetsMeta[] = compact('name', 'path');
}

$result = ['shared_count' => count($shared), 'sheets' => []];

foreach ($sheetsMeta as $meta) {
    $xml = $zip->getFromName($meta['path']);
    if ($xml === false) {
        // try worksheets relative
        $alt = 'xl/worksheets/'.basename($meta['path']);
        $xml = $zip->getFromName($alt);
        $meta['path'] = $alt;
    }
    if ($xml === false) {
        $result['sheets'][] = ['name' => $meta['name'], 'error' => 'missing '.$meta['path']];
        continue;
    }

    $sx = @simplexml_load_string($xml);
    if (! $sx) {
        $result['sheets'][] = ['name' => $meta['name'], 'error' => 'xml parse failed'];
        continue;
    }
    $sx->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

    $rowsOut = [];
    $rowCount = 0;
    foreach ($sx->sheetData->row as $row) {
        $rowCount++;
        if ($rowCount > $maxRows) {
            break;
        }
        $cells = [];
        foreach ($row->c as $c) {
            $ref = (string) $c['r'];
            $type = (string) $c['t'];
            $raw = isset($c->v) ? (string) $c->v : '';
            if ($type === 's') {
                $val = $shared[(int) $raw] ?? '';
            } elseif ($type === 'inlineStr') {
                $val = isset($c->is->t) ? (string) $c->is->t : '';
            } else {
                $val = $raw;
            }
            $cells[$ref] = trim(preg_replace('/\s+/u', ' ', $val));
        }
        $rowsOut[] = $cells;
    }

    // Flatten to dense arrays by max column letter in these rows
    $dense = [];
    foreach ($rowsOut as $cells) {
        $line = [];
        foreach ($cells as $ref => $val) {
            if (preg_match('/^([A-Z]+)/', $ref, $m)) {
                $line[$m[1]] = $val;
            }
        }
        ksort($line, SORT_STRING);
        $dense[] = $line;
    }

    $result['sheets'][] = [
        'name' => $meta['name'],
        'path' => $meta['path'],
        'rows_sparse' => $rowsOut,
        'rows_by_col' => $dense,
    ];

    fwrite(STDOUT, $meta['name'].' rows='.count($rowsOut)."\n");
}

$zip->close();
file_put_contents($out, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fwrite(STDOUT, "Wrote $out\n");
